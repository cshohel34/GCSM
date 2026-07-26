<?php

namespace App\Http\Controllers;

use App\Models\CompanyLicense;
use App\Exports\AccountingReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class CompanyLicenseController extends Controller
{
    public function index(Request $request)
    {
        $licenses = CompanyLicense::query()
            ->when($request->get('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->get('q'), fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->orderBy('expiry_date')
            ->paginate(20)->withQueryString();
        return view('license.index', ['licenses' => $licenses, 'filters' => $request->only(['status', 'q'])]);
    }

    public function create() { return view('license.form', ['license' => new CompanyLicense()]); }

    public function store(Request $request)
    {
        $license = CompanyLicense::create($this->validated($request));
        $this->storeScan($request, $license);
        return redirect()->route('license.index')->with('status', 'Licence added.');
    }

    public function edit(CompanyLicense $license) { return view('license.form', ['license' => $license]); }

    public function update(Request $request, CompanyLicense $license)
    {
        $license->update($this->validated($request));
        $this->storeScan($request, $license);
        return redirect()->route('license.index')->with('status', 'Licence updated.');
    }

    public function destroy(CompanyLicense $license)
    {
        $license->delete();
        return redirect()->route('license.index')->with('status', 'Licence removed.');
    }

    public function history(CompanyLicense $license)
    {
        $audits = $license->audits()->with('user')->latest()->get();
        return view('license.history', compact('license', 'audits'));
    }

    public function export(Request $request)
    {
        $licenses = CompanyLicense::orderBy('expiry_date')->get();
        $columns = ['Licence', 'Number', 'Authority', 'Issue', 'Expiry', 'Status'];
        $rows = [];
        foreach ($licenses as $l) {
            $rows[] = [$l->name, $l->license_no, $l->issuing_authority,
                optional($l->issue_date)->toDateString(), optional($l->expiry_date)->toDateString(), ucfirst($l->status)];
        }
        $title = 'Company Licence Register'; $meta = ['Generated' => now()->toDateString(), 'Count' => count($rows)];
        if ($request->get('export') === 'excel') {
            return Excel::download(new AccountingReportExport($title, $meta, $columns, $rows), 'Licences-'.now()->format('Ymd').'.xlsx');
        }
        return Pdf::loadView('pdf.report', ['title'=>$title,'meta'=>$meta,'columns'=>$columns,'rows'=>$rows,'numeric'=>[]])->setPaper('a4','landscape')->download('Licences-'.now()->format('Ymd').'.pdf');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'license_no' => ['nullable', 'string', 'max:120'],
            'issuing_authority' => ['nullable', 'string', 'max:191'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    protected function storeScan(Request $request, CompanyLicense $license): void
    {
        $request->validate(['scan' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:8192']]);
        if ($request->hasFile('scan')) {
            $license->update(['scan_path' => $request->file('scan')->store('licenses', 'public')]);
        }
    }
}
