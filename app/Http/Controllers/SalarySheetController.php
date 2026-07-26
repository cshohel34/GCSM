<?php

namespace App\Http\Controllers;

use App\Exports\SalarySheetExport;
use App\Models\Placement;
use App\Models\Principal;
use App\Models\SalarySheet;
use App\Models\SalaryLine;
use App\Models\Account;
use App\Services\PostingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class SalarySheetController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['principal_id', 'month', 'status']);
        $sheets = SalarySheet::with('principal', 'vessel')
            ->withCount('lines')
            ->search($filters)
            ->latest()
            ->paginate(20)->withQueryString();

        return view('salary.index', [
            'sheets' => $sheets,
            'filters' => $filters,
            'principals' => Principal::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('salary.create', [
            'principals' => Principal::with('vessels')->orderBy('name')->get(),
        ]);
    }

    /** Generate a sheet + a line per onboard crew (SM-01), pre-filled from placements (SM-02). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'principal_id' => ['required', 'exists:principals,id'],
            'principal_vessel_id' => ['nullable', 'exists:principal_vessels,id'],
            'month' => ['required', 'string', 'max:20'],
            'usd_rate' => ['required', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:120'],
        ]);

        $sheet = DB::transaction(function () use ($data, $request) {
            $sheet = SalarySheet::create([
                'principal_id' => $data['principal_id'],
                'principal_vessel_id' => $data['principal_vessel_id'] ?? null,
                'month' => strtoupper($data['month']),
                'usd_rate' => $data['usd_rate'],
                'reference' => $data['reference'] ?? null,
                'status' => 'draft',
                'created_by' => $request->user()->id,
            ]);

            $placements = Placement::with('crewProfile', 'vessel')
                ->where('principal_id', $data['principal_id'])
                ->where('status', 'onboard')
                ->when($data['principal_vessel_id'] ?? null, fn ($q, $v) => $q->where('principal_vessel_id', $v))
                ->get();

            $i = 1;
            foreach ($placements as $p) {
                $line = new SalaryLine([
                    'salary_sheet_id' => $sheet->id,
                    'crew_profile_id' => $p->crew_profile_id,
                    'placement_id' => $p->id,
                    'sl_no' => $i++,
                    'crew_name' => optional($p->crewProfile)->name,
                    'ship_name' => optional($p->vessel)->vessel_name,
                    'rank' => $p->rank,
                    'month' => $sheet->month,
                    'usd_rate' => $sheet->usd_rate,
                    'salary_usd' => $p->monthly_salary_usd ?? 0,
                    'agent_fee_usd' => $p->agency_fee_usd ?? 0,
                    'joining_date' => $p->sign_on_date,
                    'total_days' => 30,
                    'working_days' => 30,
                    'remarks' => optional(optional($p->crewProfile)->bankAccounts?->first())->account_number
                        ? 'A/C: '.$p->crewProfile->bankAccounts->first()->account_name.' — '.$p->crewProfile->bankAccounts->first()->account_number
                        : null,
                ]);
                $line->save(); // recalc via observer
            }
            return $sheet;
        });

        return redirect()->route('salary.show', $sheet)->with('status', 'Salary sheet generated. Review and edit the lines.');
    }

    public function show(SalarySheet $salary)
    {
        $salary->load('principal', 'vessel', 'lines.crewProfile', 'approvedBy');
        return view('salary.show', ['sheet' => $salary]);
    }

    public function updateLine(Request $request, SalarySheet $salary, SalaryLine $line)
    {
        $this->assertEditable($salary);
        abort_unless($line->salary_sheet_id === $salary->id, 404);
        $data = $request->validate([
            'salary_usd' => ['required', 'numeric', 'min:0'],
            'bonus_usd' => ['nullable', 'numeric', 'min:0'],
            'total_days' => ['required', 'integer', 'min:1', 'max:31'],
            'working_days' => ['required', 'integer', 'min:0', 'max:31'],
            'deduct_days' => ['nullable', 'integer', 'min:0', 'max:31'],
            'transfer_charge_usd' => ['nullable', 'numeric', 'min:0'],
            'agent_fee_usd' => ['nullable', 'numeric', 'min:0'],
            'agent_fee_charge_usd' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
            'company_amount' => ['nullable', 'numeric', 'min:0'],
        ]);
        $line->fill($data);
        $line->usd_rate = $salary->usd_rate; // keep in sync with sheet rate
        $line->save();
        return back()->with('status', 'Line updated.');
    }

    public function addLine(Request $request, SalarySheet $salary)
    {
        $this->assertEditable($salary);
        $data = $request->validate([
            'crew_profile_id' => ['nullable', 'exists:crew_profiles,id'],
            'crew_name' => ['required', 'string', 'max:191'],
            'rank' => ['nullable', 'string', 'max:120'],
            'salary_usd' => ['required', 'numeric', 'min:0'],
        ]);
        $line = new SalaryLine(array_merge($data, [
            'salary_sheet_id' => $salary->id,
            'ship_name' => optional($salary->vessel)->vessel_name,
            'month' => $salary->month,
            'usd_rate' => $salary->usd_rate,
            'sl_no' => ($salary->lines()->max('sl_no') ?? 0) + 1,
            'total_days' => 30, 'working_days' => 30,
        ]));
        $line->save();
        return back()->with('status', 'Line added.');
    }

    public function removeLine(SalarySheet $salary, SalaryLine $line)
    {
        $this->assertEditable($salary);
        abort_unless($line->salary_sheet_id === $salary->id, 404);
        $line->delete();
        return back()->with('status', 'Line removed.');
    }

    public function uploadCompanySheet(Request $request, SalarySheet $salary)
    {
        $this->assertEditable($salary);
        $request->validate(['company_sheet' => ['required', 'file', 'mimes:pdf,xlsx,xls,csv', 'max:10240']]);
        $salary->update(['company_sheet_path' => $request->file('company_sheet')->store('salary/company-sheets', 'public')]);
        return back()->with('status', 'Company sheet uploaded. Enter each line\'s company amount, then reconcile.');
    }

    public function reconcile(SalarySheet $salary)
    {
        $this->assertEditable($salary);
        // SM-03: only reconcile when every line matches the company-sent amount.
        $mismatch = $salary->lines->filter(function ($l) {
            return $l->company_amount === null || abs((float) $l->company_amount - (float) $l->net_usd) > 0.01;
        });
        if ($mismatch->isNotEmpty()) {
            return back()->withErrors(['reconcile' => $mismatch->count().' line(s) do not match the company amount (or are blank). Fix them before reconciling.']);
        }
        $salary->update(['status' => 'reconciled']);
        return back()->with('status', 'Reconciled — matches the company sheet. Ready for Super Admin approval.');
    }

    /** Approve + lock (SM-08). Requires salary.approve (Super Admin). */
    public function approve(Request $request, SalarySheet $salary)
    {
        abort_unless($request->user()->can('salary.approve'), 403);
        if ($salary->status !== 'reconciled') {
            throw ValidationException::withMessages(['status' => 'Sheet must be reconciled before approval.']);
        }
        $salary->update([
            'status' => 'locked',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);
        $this->postToAccounting($salary, $request->user()->id);
        return back()->with('status', 'Sheet approved, locked and posted to accounts.');
    }

    public function destroy(SalarySheet $salary)
    {
        $this->assertEditable($salary);
        $salary->delete();
        return redirect()->route('salary.index')->with('status', 'Sheet deleted.');
    }

    public function pdf(SalarySheet $salary)
    {
        $salary->load('principal', 'vessel', 'lines');
        return Pdf::loadView('pdf.salary_sheet', ['sheet' => $salary])
            ->setPaper('a4', 'landscape')
            ->download('SalarySheet-'.$salary->month.'-'.$salary->id.'.pdf');
    }

    public function excel(SalarySheet $salary)
    {
        return Excel::download(new SalarySheetExport($salary), 'SalarySheet-'.$salary->month.'-'.$salary->id.'.xlsx');
    }

    /** Auto-post the salary sheet to the ledger (disbursement + agency-fee revenue). */
    protected function postToAccounting(SalarySheet $salary, int $userId): void
    {
        if ($salary->accounting_txn_id) return;
        $salary->loadMissing('lines');
        $netBdt = round((float) $salary->lines->sum('net_bdt'), 2);
        $agentBdt = round((float) $salary->lines->sum('agent_net_bdt'), 2);
        $acc = fn ($code) => optional(Account::where('code', $code)->first())->id;
        $lines = [];
        if ($netBdt > 0 && $acc('2110') && $acc('1120')) {
            $lines[] = ['account_id' => $acc('2110'), 'debit' => $netBdt, 'credit' => 0, 'memo' => 'Crew salary disbursed'];
            $lines[] = ['account_id' => $acc('1120'), 'debit' => 0, 'credit' => $netBdt, 'memo' => 'Bank BDT'];
        }
        if ($agentBdt > 0 && $acc('1210') && $acc('4010')) {
            $lines[] = ['account_id' => $acc('1210'), 'debit' => $agentBdt, 'credit' => 0, 'memo' => 'Agency fee receivable', 'party_type' => 'principal', 'party_id' => $salary->principal_id];
            $lines[] = ['account_id' => $acc('4010'), 'debit' => 0, 'credit' => $agentBdt, 'memo' => 'Agency fee income'];
        }
        if (count($lines) < 2) return;
        try {
            $txn = app(PostingService::class)->record([
                'voucher_type' => 'journal', 'date' => now()->toDateString(),
                'narration' => 'Salary sheet '.$salary->month.' — '.optional($salary->principal)->name,
                'reference' => $salary->reference, 'created_by' => $userId,
                'source_type' => 'SalarySheet', 'source_id' => $salary->id,
            ], $lines);
            $salary->update(['accounting_txn_id' => $txn->id]);
        } catch (\Throwable $e) {
            \Log::warning('[Salary auto-post] '.$e->getMessage());
        }
    }

    protected function assertEditable(SalarySheet $salary): void
    {
        if (! $salary->isEditable()) {
            abort(403, 'This sheet is locked and cannot be modified.');
        }
    }
}
