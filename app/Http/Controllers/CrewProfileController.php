<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCrewProfileRequest;
use App\Http\Requests\UpdateCrewProfileRequest;
use App\Models\CourseCatalogue;
use App\Models\CrewProfile;
use App\Models\Rank;
use App\Models\VesselType;
use App\Models\PendingChange;
use App\Services\DuplicateCrewChecker;
use App\Exports\CrewListExport;
use App\Services\CvPdfExtractor;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CrewProfileController extends Controller
{

    public function index(Request $request)
    {
        $filters = $request->only([
            'name','cdc_no','passport_no','coc_no','mobile','email','admission_id',
            'rank_id','availability','vessel_type','company_name','vessel_name','owner',
            'duration_from','duration_to',
        ]);

        $crew = CrewProfile::query()
            ->with('currentRank')
            ->withCount('offences')
            ->search($filters)
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Highlight urgent-job crew when searching by a rank (CM-16).
        $urgentByRank = ($filters['rank_id'] ?? null)
            ? CrewProfile::where('current_rank_id', $filters['rank_id'])
                ->where('job_urgency', 'urgent')->pluck('id')->all()
            : [];

        return view('crew.index', [
            'crew' => $crew,
            'filters' => $filters,
            'ranks' => Rank::where('active', true)->orderBy('sort_order')->orderBy('rank_name')->get(),
            'vesselTypes' => VesselType::where('active', true)->orderBy('type_name')->get(),
            'urgentByRank' => $urgentByRank,
        ]);
    }

    /** Shared data for the crew create/edit form (rank dropdowns, etc.). */
    protected function formData(CrewProfile $crew): array
    {
        return [
            'crew'  => $crew,
            'ranks' => \App\Models\Rank::where('active', true)
                ->orderBy('sort_order')->orderBy('rank_name')->get(),
        ];
    }

    public function create(Request $request)
    {
        $mode = $request->query('mode');
        // First ask how they want to add the crew: from a CV or by hand.
        if (! in_array($mode, ['cv', 'manual'], true)) {
            return view('crew.choose');
        }
        return view('crew.form', $this->formData(new CrewProfile()) + ['mode' => $mode]);
    }

    public function store(StoreCrewProfileRequest $request, DuplicateCrewChecker $dup)
    {
        $data = $request->validated();

        // Never allow a duplicate crew profile.
        $matches = $dup->find($data);
        if ($matches->isNotEmpty()) {
            return back()->withInput()
                ->with('duplicates', $matches->map(fn ($m) => [
                    'id' => $m['crew']->id, 'name' => $m['crew']->name,
                    'display_id' => $m['crew']->display_id, 'reason' => $m['reason'],
                ])->all())
                ->withErrors(['duplicate' => 'A matching crew profile already exists — creation blocked.']);
        }

        $data['source'] = 'manual';
        $data['created_by'] = $request->user()->id;
        $data['gc_id'] = CrewProfile::generateGcId();   // manual crew get a GCSM ID (no admission ID)
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('crew/photos', 'public');
        }
        unset($data['photo']);
        $crew = CrewProfile::create($data);

        return redirect()->route('crew.show', $crew)->with('status', 'Draft profile saved ('.$crew->gc_id.'). Complete the remaining CV fields below — nothing is lost.');
    }

    /** Read-only, organized profile view. */
    public function show(CrewProfile $crew, \App\Services\ProfileCompleteness $completeness)
    {
        return $this->renderProfile($crew, $completeness, 'crew.view');
    }

    /** Editable profile page (Complete Profile + add/remove records). */
    public function editProfile(CrewProfile $crew, \App\Services\ProfileCompleteness $completeness)
    {
        return $this->renderProfile($crew, $completeness, 'crew.show');
    }

    protected function renderProfile(CrewProfile $crew, \App\Services\ProfileCompleteness $completeness, string $view)
    {
        $crew->load([
            'currentRank', 'rankApplied', 'seaServices', 'courses.catalogue',
            'documents', 'bankAccounts', 'offences.recordedBy', 'notes.author',
            'maritimeEducations', 'academics', 'statusLogs.changedBy',
            'placements' => fn ($q) => $q->with('principal', 'vessel')->orderByDesc('sign_on_date')->orderByDesc('id'),
        ]);
        $c = $completeness->for($crew);
        // Keep the Draft/Complete flag in sync with actual completeness (no audit noise).
        $shouldDraft = ! $c['complete'];
        if ((bool) $crew->is_draft !== $shouldDraft) {
            $crew->forceFill(['is_draft' => $shouldDraft])->saveQuietly();
            $crew->is_draft = $shouldDraft;
        }

        $activePlacement = $crew->placements->firstWhere('status', 'onboard');

        // Full edit/change log (OwenIt audit trail) for the profile + its audited child records.
        $editLog = \OwenIt\Auditing\Models\Audit::with('user')
            ->where(function ($q) use ($crew) {
                $q->where(fn ($qq) => $qq->where('auditable_type', \App\Models\CrewProfile::class)->where('auditable_id', $crew->id));
                foreach ([
                    \App\Models\CrewDocument::class => $crew->documents->pluck('id'),
                    \App\Models\CrewCourse::class => $crew->courses->pluck('id'),
                    \App\Models\CrewMaritimeEducation::class => $crew->maritimeEducations->pluck('id'),
                    \App\Models\CrewAcademic::class => $crew->academics->pluck('id'),
                    \App\Models\CrewSeaService::class => $crew->seaServices->pluck('id'),
                    \App\Models\CrewBankAccount::class => $crew->bankAccounts->pluck('id'),
                    \App\Models\CrewOffence::class => $crew->offences->pluck('id'),
                    \App\Models\CrewNote::class => $crew->notes->pluck('id'),
                ] as $type => $ids) {
                    if ($ids->isNotEmpty()) {
                        $q->orWhere(fn ($qq) => $qq->where('auditable_type', $type)->whereIn('auditable_id', $ids->all()));
                    }
                }
            })
            ->latest()->limit(300)->get();

        $reminderStats = app(\App\Services\ExpiryMonitor::class)->crewCounts($crew);

        return view($view, [
            'crew' => $crew,
            'editLog' => $editLog,
            'reminderStats' => $reminderStats,
            'marineAcademies' => \App\Models\MarineAcademy::where('active', true)->orderBy('category')->orderBy('name')->get()->groupBy('category'),
            'marineDepartments' => \App\Models\MarineDepartment::where('active', true)->orderBy('category')->orderBy('name')->get()->groupBy('category'),
            'vesselTypes' => VesselType::where('active', true)->orderBy('type_name')->get(),
            'ranks' => Rank::where('active', true)->orderBy('sort_order')->orderBy('rank_name')->get(),
            'courses' => CourseCatalogue::where('active', true)->orderBy('course_name')->get(),
            'reminderCount' => \App\Models\AppNotification::where('crew_profile_id', $crew->id)->where('user_id', auth()->id())->count(),
            'completeness' => $c,
            'activePlacement' => $activePlacement,
        ]);
    }

    public function edit(CrewProfile $crew)
    {
        return view('crew.form', $this->formData($crew));
    }

    public function update(UpdateCrewProfileRequest $request, CrewProfile $crew)
    {
        $data = $request->validated();
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('crew/photos', 'public');
        }
        unset($data['photo']);

        // Managers/Super Admins edit live; everyone else's edit waits for approval.
        if ($request->user()->hasAnyRole(['Super Admin', 'Admin'])) {
            $crew->update($data);
            return redirect()->route('crew.show', $crew)->with('status', 'Crew profile updated.');
        }

        $diff = [];
        foreach ($data as $k => $v) {
            $old = $crew->getAttribute($k);
            if ((string) $old !== (string) $v) $diff[$k] = ['old' => $old, 'new' => $v];
        }
        if (! $diff) {
            return redirect()->route('crew.show', $crew)->with('status', 'No changes.');
        }
        PendingChange::create([
            'subject_type' => CrewProfile::class, 'subject_id' => $crew->id,
            'label' => $crew->name, 'changes' => $diff,
            'reason' => $request->input('change_reason'),
            'status' => 'pending', 'requested_by' => $request->user()->id,
        ]);
        return redirect()->route('crew.show', $crew)->with('status', 'Your changes were sent to a Manager/Super Admin for approval.');
    }

    /**
     * Save the extended CV "Complete Profile" personal fields (physical, next of
     * kin, English level, addresses). Managers/Super Admins apply live; other
     * staff edits queue for approval — same rule as update().
     */
    public function updateDetails(Request $request, CrewProfile $crew)
    {
        $data = $request->validate([
            'place_of_birth' => ['nullable', 'string', 'max:191'],
            'nationality' => ['nullable', 'string', 'max:120'],
            'religion' => ['nullable', 'string', 'max:120'],
            'gender' => ['nullable', \Illuminate\Validation\Rule::in(['Male', 'Female'])],
            'marital_status' => ['nullable', \Illuminate\Validation\Rule::in(['Single','Married','Widowed','Separated','Divorced','Not specified'])],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'height_cm' => ['nullable', 'string', 'max:20'],
            'weight_kg' => ['nullable', 'string', 'max:20'],
            'shoe_size' => ['nullable', 'string', 'max:20'],
            'coverall_size' => ['nullable', 'string', 'max:20'],
            'present_address' => ['nullable', 'string', 'max:500'],
            'permanent_address' => ['nullable', 'string', 'max:500'],
            'emergency_contact' => ['nullable', 'string', 'max:60'],
            'next_of_kin_name' => ['nullable', 'string', 'max:191'],
            'next_of_kin_relation' => ['nullable', 'string', 'max:120'],
            'next_of_kin_contact' => ['nullable', 'string', 'max:60'],
            'next_of_kin_address' => ['nullable', 'string', 'max:500'],
            'english_listening' => ['nullable', \Illuminate\Validation\Rule::in(['Excellent','Very Good','Good','Fair','Poor'])],
            'english_speaking' => ['nullable', \Illuminate\Validation\Rule::in(['Excellent','Very Good','Good','Fair','Poor'])],
            'english_reading' => ['nullable', \Illuminate\Validation\Rule::in(['Excellent','Very Good','Good','Fair','Poor'])],
            'english_writing' => ['nullable', \Illuminate\Validation\Rule::in(['Excellent','Very Good','Good','Fair','Poor'])],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('crew/photos', 'public');
        }
        unset($data['photo']);

        if ($request->user()->hasAnyRole(['Super Admin', 'Admin'])) {
            $crew->update($data);
            return redirect()->route('crew.show', $crew)->with('status', 'Profile details saved.');
        }

        $diff = [];
        foreach ($data as $k => $v) {
            $old = $crew->getAttribute($k);
            if ((string) $old !== (string) $v) $diff[$k] = ['old' => $old, 'new' => $v];
        }
        if (! $diff) return redirect()->route('crew.show', $crew)->with('status', 'No changes.');
        PendingChange::create([
            'subject_type' => CrewProfile::class, 'subject_id' => $crew->id,
            'label' => $crew->name, 'changes' => $diff, 'reason' => 'Complete profile details',
            'status' => 'pending', 'requested_by' => $request->user()->id,
        ]);
        return redirect()->route('crew.show', $crew)->with('status', 'Details sent to a Manager/Super Admin for approval.');
    }

    /**
     * Delete a crew profile. Super-Admin-only (enforced on the route) and the
     * admin must re-enter their own account password. The record is soft-deleted
     * (retained) so it can be restored later from the Recycle Bin.
     */
    public function destroy(Request $request, CrewProfile $crew)
    {
        $request->validate(['password' => ['required', 'string']]);

        if (! Hash::check($request->input('password'), $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => 'The password you entered is incorrect. The crew profile was not deleted.',
            ]);
        }

        $name = $crew->name;
        $crew->delete(); // soft delete — retained and restorable from the Recycle Bin

        return redirect()->route('crew.index')
            ->with('status', "Crew profile \"{$name}\" was moved to the Recycle Bin. You can restore it any time.");
    }

    /** Recycle Bin — soft-deleted crew profiles (Super Admin only). */
    public function trash()
    {
        $crew = CrewProfile::onlyTrashed()
            ->with('currentRank')
            ->orderByDesc('deleted_at')
            ->paginate(20);

        return view('crew.trash', ['crew' => $crew]);
    }

    /**
     * Restore a soft-deleted crew profile (Super Admin only). Like deletion, the
     * admin must re-enter their own account password to confirm.
     */
    public function restore(Request $request, $id)
    {
        $request->validate(['password' => ['required', 'string']]);

        $crew = CrewProfile::onlyTrashed()->findOrFail($id);

        if (! Hash::check($request->input('password'), $request->user()->password)) {
            return redirect()->route('crew.trash')
                ->withErrors(['password' => 'The password you entered is incorrect. The crew profile was not restored.'])
                ->with('restore_error', [
                    'action' => route('crew.restore', $crew->id),
                    'name'   => $crew->name,
                    'id'     => $crew->display_id,
                ]);
        }

        $crew->restore();

        return redirect()->route('crew.trash')
            ->with('status', "Crew profile \"{$crew->name}\" has been restored.");
    }

    public function toggleAvailability(Request $request, CrewProfile $crew, \App\Services\CrewStatusUpdater $updater)
    {
        $next = $crew->availability === 'available' ? 'not_available' : 'available';
        $updater->apply($crew, ['availability' => $next], 'placement_history', 'Quick availability toggle', $request->user()->id);
        return back()->with('status', 'Availability updated.');
    }

    /**
     * Edit availability & job-urgency from the Placement History tab.
     * A reason is required; the change syncs system-wide and is logged.
     */
    public function updateStatus(Request $request, CrewProfile $crew, \App\Services\CrewStatusUpdater $updater)
    {
        $data = $request->validate([
            'availability'   => ['required', \Illuminate\Validation\Rule::in(['available', 'not_available', 'onboard', 'resting'])],
            'job_urgency'    => ['required', \Illuminate\Validation\Rule::in(['normal', 'high', 'urgent'])],
            'job_deadline'   => ['nullable', 'date'],
            'available_from' => ['nullable', 'date'],
            'reason'         => ['required', 'string', 'max:500'],
        ]);

        $changed = $updater->apply($crew, $data, 'placement_history', $data['reason'], $request->user()->id);

        return redirect()->route('crew.editprofile', $crew)
            ->with('status', $changed ? 'Availability & urgency updated and logged.' : 'No change — values were already set.');
    }

    public function export(Request $request)
    {
        $filters = $request->only([
            'name','cdc_no','passport_no','coc_no','mobile','email','admission_id',
            'rank_id','availability','vessel_type','company_name','vessel_name','owner',
            'duration_from','duration_to',
        ]);
        $crew = CrewProfile::with('currentRank')->search($filters)->orderBy('name')->get();
        if ($request->get('export') === 'excel') {
            return Excel::download(new CrewListExport($crew), 'Crew-List-'.now()->format('Ymd').'.xlsx');
        }
        return Pdf::loadView('pdf.crew_list', ['crew' => $crew])->setPaper('a4', 'landscape')
            ->download('Crew-List-'.now()->format('Ymd').'.pdf');
    }

    public function uploadCv(Request $request, CrewProfile $crew, CvPdfExtractor $extractor)
    {
        $request->validate(['cv' => ['required', 'file', 'mimes:pdf', 'max:8192']]);
        $path = $request->file('cv')->store('crew/cv-uploads', 'public');
        try {
            $data = $extractor->extract(storage_path('app/public/'.$path));
            $profile = $data['profile'] ?? [];
            // Fill only currently-empty fields — never overwrite existing data.
            $fill = [];
            foreach ($profile as $k => $v) {
                if ($v !== null && $v !== '' && blank($crew->getAttribute($k))) $fill[$k] = $v;
            }
            if ($fill) $crew->update($fill);
            return back()->with('status', 'CV read — filled '.count($fill).' empty field(s). Please review before saving.');
        } catch (\Throwable $e) {
            return back()->with('status', 'Could not read this CV. Please fill the form manually.');
        }
    }

    /** Parse an uploaded CV (create-form auto-fill) and return the extracted fields as JSON. */
    public function parseCv(Request $request, CvPdfExtractor $extractor)
    {
        $request->validate(['cv' => ['required', 'file', 'mimes:pdf', 'max:8192']]);
        try {
            $path = $request->file('cv')->store('crew/cv-uploads', 'public');
            $data = $extractor->extract(storage_path('app/public/'.$path));
            return response()->json(['ok' => true, 'profile' => $data['profile'] ?? []]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'Could not read this CV. Please fill the form manually.']);
        }
    }
}
          