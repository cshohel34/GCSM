<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrincipalRequest;
use App\Http\Requests\UpdatePrincipalRequest;
use App\Models\Principal;
use App\Models\PrincipalStaffAssignment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\AccountingReportExport;
use Maatwebsite\Excel\Facades\Excel;

class PrincipalController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['name', 'type', 'country', 'status', 'staff_id']);
        $principals = Principal::query()
            ->with('assignedStaff')
            ->withCount(['currentCrew as onboard_count'])
            ->withCount('offences')
            ->search($filters)
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('principal.index', [
            'principals' => $principals,
            'filters' => $filters,
            'staff' => User::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('principal.form', [
            'principal' => new Principal(),
            'staff' => User::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(StorePrincipalRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('principals/logos', 'public');
        }
        unset($data['logo']);
        $data['status'] = 'inactive'; // can be activated any time (contract optional)
        $data['created_by'] = $request->user()->id;
        $principal = Principal::create($data);
        if ($principal->assigned_staff_id) {
            $this->recordAssignment($principal, $principal->assigned_staff_id, 'Initial assignment', $request->user());
        }
        return redirect()->route('principal.show', $principal)->with('status', 'Company created.');
    }

    /** Read-only tabbed company profile (mirrors the crew profile view). */
    public function show(Principal $principal)
    {
        $principal->load([
            'contacts', 'vessels', 'documents', 'companyNotes.author', 'offences.recordedBy',
            'assignedStaff', 'createdBy',
            'assignments.staff', 'assignments.assignedBy',
            'placements.crewProfile', 'placements.vessel',
            'salarySheets.vessel',
        ]);
        return view('principal.view', [
            'principal' => $principal,
            'onboard' => $principal->placements->where('status', 'onboard'),
            'past' => $principal->placements->where('status', 'signed_off'),
            'editLog' => $this->buildEditLog($principal),
        ]);
    }

    /** Editable tabbed company page (mirrors the crew edit-profile page). */
    public function editProfile(Principal $principal)
    {
        $principal->load([
            'contacts', 'vessels', 'documents', 'companyNotes.author', 'offences.recordedBy',
            'assignedStaff', 'createdBy',
            'assignments.staff', 'assignments.assignedBy',
            'placements.crewProfile', 'placements.vessel',
            'salarySheets.vessel',
        ]);
        return view('principal.editprofile', [
            'principal' => $principal,
            'staff' => User::where('status', 'active')->orderBy('name')->get(),
            'vesselTypes' => \App\Models\VesselType::where('active', true)->orderBy('type_name')->get(),
            'onboard' => $principal->placements->where('status', 'onboard'),
            'past' => $principal->placements->where('status', 'signed_off'),
            'editLog' => $this->buildEditLog($principal),
        ]);
    }

    /** Full audit trail for the company and its audited child records. */
    protected function buildEditLog(Principal $principal)
    {
        return \OwenIt\Auditing\Models\Audit::with('user')
            ->where(function ($q) use ($principal) {
                $q->where(fn ($qq) => $qq->where('auditable_type', \App\Models\Principal::class)->where('auditable_id', $principal->id));
                foreach ([
                    \App\Models\PrincipalContact::class  => $principal->contacts->pluck('id'),
                    \App\Models\PrincipalVessel::class   => $principal->vessels->pluck('id'),
                    \App\Models\PrincipalDocument::class => $principal->documents->pluck('id'),
                    \App\Models\PrincipalNote::class     => $principal->companyNotes->pluck('id'),
                    \App\Models\PrincipalOffence::class  => $principal->offences->pluck('id'),
                ] as $type => $ids) {
                    if ($ids->isNotEmpty()) {
                        $q->orWhere(fn ($qq) => $qq->where('auditable_type', $type)->whereIn('auditable_id', $ids->all()));
                    }
                }
            })
            ->latest()->limit(300)->get();
    }

    public function edit(Principal $principal)
    {
        return view('principal.form', [
            'principal' => $principal,
            'staff' => User::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdatePrincipalRequest $request, Principal $principal)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('principals/logos', 'public');
        }
        unset($data['logo']);

        // Managing staff is managed on the Edit Profile "Managing Staff" tab (multiple allowed),
        // so it is not changed from the company-info form.
        unset($data['assigned_staff_id']);

        $principal->update($data);

        return redirect()->route('principal.show', $principal)->with('status', 'Company updated.');
    }

    public function destroy(Principal $principal)
    {
        $principal->delete();
        return redirect()->route('principal.index')->with('status', 'Principal deleted.');
    }

    public function activate(Request $request, Principal $principal)
    {
        // A company can be activated with or without a contract on file.
        $principal->update(['status' => $principal->status === 'active' ? 'inactive' : 'active']);
        return back()->with('status', $principal->status === 'active' ? 'Company activated.' : 'Company deactivated.');
    }

    /**
     * Add a managing staff member. A company may have two or more concurrent
     * managers, so this is additive — it does not unassign existing managers.
     */
    public function assignStaff(Request $request, Principal $principal)
    {
        $data = $request->validate([
            'staff_id' => ['required', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $already = PrincipalStaffAssignment::where('principal_id', $principal->id)
            ->where('staff_id', $data['staff_id'])
            ->whereNull('unassigned_at')->exists();
        if ($already) {
            return back()->with('status', 'That staff member already manages this company.');
        }

        PrincipalStaffAssignment::create([
            'principal_id' => $principal->id,
            'staff_id'     => (int) $data['staff_id'],
            'assigned_by'  => $request->user()->id,
            'reason'       => $data['reason'] ?? null,
            'assigned_at'  => now(),
        ]);
        // Keep a single representative manager for list/search columns.
        $principal->update(['assigned_staff_id' => (int) $data['staff_id']]);

        return back()->with('status', 'Managing staff added.');
    }

    /** Remove a managing staff member (closes their assignment; history is kept). */
    public function removeStaff(Request $request, Principal $principal, PrincipalStaffAssignment $assignment)
    {
        abort_unless($assignment->principal_id === $principal->id, 404);

        if (is_null($assignment->unassigned_at)) {
            $assignment->update(['unassigned_at' => now()]);
        }
        // If we just removed the representative manager, repoint to another active one.
        if ((int) $principal->assigned_staff_id === (int) $assignment->staff_id) {
            $next = PrincipalStaffAssignment::where('principal_id', $principal->id)
                ->whereNull('unassigned_at')->latest('assigned_at')->first();
            $principal->update(['assigned_staff_id' => $next?->staff_id]);
        }

        return back()->with('status', 'Managing staff removed.');
    }

    public function directoryPdf()
    {
        $principals = Principal::with('contacts', 'assignedStaff')->orderBy('name')->get();
        return Pdf::loadView('pdf.principal_directory', compact('principals'))
            ->setPaper('a4', 'landscape')
            ->download('GCSM-Principal-Directory.pdf');
    }

    public function crewExport(Request $request, Principal $principal)
    {
        // scope: all (default) | onboard | past
        $scope = $request->get('scope', 'all');
        $query = $principal->placements()->with('crewProfile', 'vessel');
        if ($scope === 'onboard') {
            $query->where('status', 'onboard');
        } elseif ($scope === 'past') {
            $query->where('status', 'signed_off');
        }
        $placements = $query->get();
        $columns = ['Crew', 'Rank', 'Vessel', 'Status', 'Sign On', 'Sign Off', 'Salary USD', 'Agency Fee USD'];
        $rows = [];
        foreach ($placements as $pl) {
            $rows[] = [
                optional($pl->crewProfile)->name, $pl->rank, optional($pl->vessel)->vessel_name,
                ucfirst($pl->status), optional($pl->sign_on_date)->toDateString(), optional($pl->sign_off_date)->toDateString(),
                number_format((float) $pl->monthly_salary_usd, 2), number_format((float) $pl->agency_fee_usd, 2),
            ];
        }
        $title = 'Crew & Salary — '.$principal->name;
        $meta = ['Company' => $principal->name, 'Type' => ucfirst($principal->type), 'Generated' => now()->toDateString()];
        $numeric = [6,7];
        if ($request->get('export') === 'excel') {
            return Excel::download(new AccountingReportExport($title, $meta, $columns, $rows), 'Company-Crew-'.$principal->id.'.xlsx');
        }
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.report', compact('title','meta','columns','rows','numeric'))->setPaper('a4','landscape')->download('Company-Crew-'.$principal->id.'.pdf');
    }

    protected function recordAssignment(Principal $principal, int $staffId, ?string $reason, User $by): void
    {
        DB::transaction(function () use ($principal, $staffId, $reason, $by) {
            PrincipalStaffAssignment::where('principal_id', $principal->id)
                ->whereNull('unassigned_at')
                ->update(['unassigned_at' => now()]);
            PrincipalStaffAssignment::create([
                'principal_id' => $principal->id,
                'staff_id' => $staffId,
                'assigned_by' => $by->id,
                'reason' => $reason,
                'assigned_at' => now(),
            ]);
            $principal->update(['assigned_staff_id' => $staffId]);
        });
    }
}
