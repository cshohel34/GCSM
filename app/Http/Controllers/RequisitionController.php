<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequisitionRequest;
use App\Http\Requests\UpdateRequisitionRequest;
use App\Models\CrewProfile;
use App\Models\Principal;
use App\Models\Rank;
use App\Models\Requisition;
use App\Models\RequisitionPosition;
use App\Exports\AccountingReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class RequisitionController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only([
            'principal_id', 'status', 'reference', 'date_from', 'date_to',
            'vessel_id', 'rank_id', 'country', 'contact_id', 'staff_id',
        ]);
        $requisitions = Requisition::with(['principal', 'contact', 'assignedStaff', 'createdBy'])
            ->withCount('positions')
            ->search($filters)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('selection.index', [
            'requisitions' => $requisitions,
            'filters' => $filters,
            'principals' => Principal::orderBy('name')->get(),
            // Same source & ordering as the crew module so the grouped rank list matches.
            'ranks' => Rank::where('active', true)->orderBy('sort_order')->orderBy('rank_name')->get(),
            // Every vessel / contact across all companies, grouped by company in the dropdown.
            'vessels' => \App\Models\PrincipalVessel::with('principal')
                ->orderBy('vessel_name')->get()->groupBy(fn ($v) => optional($v->principal)->name ?: 'Unassigned'),
            'contacts' => \App\Models\PrincipalContact::with('principal')
                ->orderBy('name')->get()->groupBy(fn ($c) => optional($c->principal)->name ?: 'Unassigned'),
            'staff' => \App\Models\User::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('selection.form', [
            'requisition' => new Requisition(),
            'principals' => Principal::with(['contacts' => fn ($q) => $q->orderBy('name')])->orderBy('name')->get(),
        ]);
    }

    public function store(StoreRequisitionRequest $request)
    {
        $data = $request->validated();
        $data['principal_contact_id'] = $this->validContactId($data['principal_id'], $data['principal_contact_id'] ?? null);
        $data['created_by'] = $request->user()->id;
        unset($data['reference']); // always auto-generated
        $requisition = Requisition::create($data);

        // Auto-generate a unique reference, e.g. REQ-2026-0007
        $requisition->update([
            'reference' => 'REQ-'.now()->format('Y').'-'.str_pad((string) $requisition->id, 4, '0', STR_PAD_LEFT),
        ]);

        return redirect()->route('selection.show', $requisition)->with('status', 'Requirement created. Add positions below.');
    }

    /** Keep the contact only when it really belongs to the chosen company. */
    protected function validContactId($principalId, $contactId): ?int
    {
        if (! $contactId) return null;
        $ok = \App\Models\PrincipalContact::where('id', $contactId)
            ->where('principal_id', $principalId)->exists();
        return $ok ? (int) $contactId : null;
    }

    public function show(Request $request, Requisition $requisition)
    {
        $requisition->load([
            'principal.vessels', 'contact', 'createdBy', 'assignedStaff',
            'positions.rank', 'positions.vessel',
            'positions.candidates.crewProfile.currentRank',
            'positions.candidates.crewProfile.offences',
            'positions.candidates.checklistItems' => fn ($q) => $q->with('remarkBy')->orderBy('sort_order')->orderBy('id'),
        ]);

        // Keep every candidate's checklist in step with the (Settings-customisable)
        // template and the crew profile. Idempotent — only writes when something changed.
        $checklist = app(\App\Services\CandidateChecklist::class);
        foreach ($requisition->positions as $position) {
            foreach ($position->candidates as $cand) {
                $checklist->sync($cand);
                $cand->load(['checklistItems' => fn ($q) => $q->with('remarkBy')->orderBy('sort_order')->orderBy('id')]);
            }
        }

        // Crew search for a specific position (adds to wishlist without leaving the page).
        // Free text matches name / CDC / passport / mobile / crew number.
        $crewMatches = collect();
        $searchPos = $request->integer('pos') ?: null;
        $q = trim((string) $request->get('q'));
        $availability = (string) $request->get('availability', '');
        $vesselType   = (string) $request->get('vessel_type', '');
        $searchRank   = (string) $request->get('crew_rank_id', '');
        $hasCriteria  = ($q !== '' || $availability !== '' || $vesselType !== '' || $searchRank !== '');

        if ($searchPos && $hasCriteria) {
            $already = \App\Models\Candidate::where('requisition_position_id', $searchPos)->pluck('crew_profile_id');
            $crewMatches = CrewProfile::with('currentRank', 'offences')
                ->whereNotIn('id', $already)
                ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x
                    ->where('name', 'like', "%{$q}%")
                    ->orWhere('cdc_no', 'like', "%{$q}%")
                    ->orWhere('passport_no', 'like', "%{$q}%")
                    ->orWhere('mobile', 'like', "%{$q}%")
                    ->orWhere('gc_id', 'like', "%{$q}%")))
                ->when($searchRank !== '', fn ($w) => $w->where('current_rank_id', $searchRank))
                ->when($availability !== '', fn ($w) => $w->where('availability', $availability))
                ->when($vesselType !== '', fn ($w) => $w->whereHas('seaServices',
                    fn ($s) => $s->where('vessel_type', 'like', "%{$vesselType}%")))
                ->orderBy('name')
                ->limit(25)->get();
        }

        return view('selection.show', [
            'requisition' => $requisition,
            'ranks' => Rank::where('active', true)->orderBy('sort_order')->orderBy('rank_name')->get(),
            'staff' => \App\Models\User::where('status', 'active')->orderBy('name')->get(),
            'vesselTypes' => \App\Models\VesselType::where('active', true)->orderBy('type_name')->get(),
            'crewMatches' => $crewMatches,
            'searchPos' => $searchPos,
            'q' => $q,
            'searchAvailability' => $availability,
            'searchVesselType' => $vesselType,
            'searchCrewRankId' => $searchRank,
        ]);
    }

    public function edit(Requisition $requisition)
    {
        return view('selection.form', [
            'requisition' => $requisition,
            'principals' => Principal::with(['contacts' => fn ($q) => $q->orderBy('name')])->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateRequisitionRequest $request, Requisition $requisition)
    {
        $data = $request->validated();
        $data['principal_contact_id'] = $this->validContactId($data['principal_id'], $data['principal_contact_id'] ?? null);

        // Once a deadline has passed, only a Super Admin may change (extend) it.
        // Everyone else keeps the existing deadline untouched.
        if ($requisition->deadlinePassed() && ! $request->user()->hasRole('Super Admin')) {
            unset($data['deadline']);
        }

        $requisition->update($data);
        return redirect()->route('selection.show', $requisition)->with('status', 'Requirement updated.');
    }

    public function destroy(Requisition $requisition)
    {
        $requisition->delete();
        return redirect()->route('selection.index')->with('status', 'Requirement deleted.');
    }

    public function close(Requisition $requisition)
    {
        $requisition->update(['status' => $requisition->status === 'open' ? 'closed' : 'open']);
        return back()->with('status', 'Requirement status updated.');
    }

    // ---- Managing staff / partners on a requirement ----
    public function addStaff(Request $request, Requisition $requisition)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'note' => ['nullable', 'string', 'max:191'],
        ]);

        if ($requisition->assignedStaff()->where('users.id', $data['user_id'])->exists()) {
            return back()->with('status', 'That staff / partner is already assigned to this requirement.');
        }

        $requisition->assignedStaff()->attach($data['user_id'], [
            'assigned_by' => $request->user()->id,
            'note' => $data['note'] ?? null,
        ]);

        return back()->with('status', 'Staff / partner assigned to this requirement.');
    }

    public function removeStaff(Requisition $requisition, \App\Models\User $user)
    {
        $requisition->assignedStaff()->detach($user->id);
        return back()->with('status', 'Staff / partner removed from this requirement.');
    }

    public function export(Request $request, Requisition $requisition)
    {
        $requisition->load('principal', 'positions.rank', 'positions.vessel', 'positions.candidates');
        $columns = ['Rank', 'Vessel', 'Need', 'Wishlist', 'Shortlist', 'Forwarded', 'Signed On', 'Status'];
        $rows = [];
        foreach ($requisition->positions as $p) {
            $rows[] = [
                optional($p->rank)->rank_name ?: 'Any', optional($p->vessel)->vessel_name, $p->headcount,
                $p->countAt(['wishlisted']), $p->countAt(['shortlisted']),
                $p->countAt(['forwarded','interview_selected','interview_passed','interview_failed','final_selected']),
                $p->countAt(['signed_on']), ucfirst($p->status),
            ];
        }
        $title = 'Requirement '.($requisition->reference ?: 'REQ-'.$requisition->id);
        $meta = ['Company' => optional($requisition->principal)->name, 'Date' => optional($requisition->requirement_date)->toDateString(), 'Status' => ucfirst($requisition->status)];
        $numeric = [2,3,4,5,6];
        if ($request->get('export') === 'excel') {
            return Excel::download(new AccountingReportExport($title, $meta, $columns, $rows), 'Requirement-'.$requisition->id.'.xlsx');
        }
        return Pdf::loadView('pdf.report', compact('title','meta','columns','rows','numeric'))->setPaper('a4','landscape')->download('Requirement-'.$requisition->id.'.pdf');
    }

    // ---- Positions ----
    public function storePosition(Request $request, Requisition $requisition)
    {
        if ($requisition->deadlinePassed()) {
            return back()->withErrors(['deadline' => 'The requirement deadline has passed — no new position can be added. A Super Admin can extend the deadline first.']);
        }
        $data = $request->validate([
            'rank_id' => ['nullable', 'exists:ranks,id'],
            'principal_vessel_id' => ['nullable', 'exists:principal_vessels,id'],
            'headcount' => ['required', 'integer', 'min:1', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);
        $requisition->positions()->create($data);
        return back()->with('status', 'Position added.');
    }

    public function destroyPosition(Requisition $requisition, RequisitionPosition $position)
    {
        abort_unless($position->requisition_id === $requisition->id, 404);
        $position->delete();
        return back()->with('status', 'Position removed.');
    }

    public function markUnfulfilled(Request $request, Requisition $requisition, RequisitionPosition $position)
    {
        abort_unless($position->requisition_id === $requisition->id, 404);
        $data = $request->validate(['unfulfilled_reason' => ['required', 'string', 'max:255']]);
        $position->update(['status' => 'unfulfilled', 'unfulfilled_reason' => $data['unfulfilled_reason']]);
        return back()->with('status', 'Position marked unfulfilled.');
    }
}
