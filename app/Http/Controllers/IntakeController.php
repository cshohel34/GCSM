<?php

namespace App\Http\Controllers;

use App\Models\CrewProfile;
use App\Models\CvSubmission;
use App\Models\PendingChange;
use App\Services\DuplicateCrewChecker;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IntakeController extends Controller
{
    public function index(DuplicateCrewChecker $dup)
    {
        $submissions = CvSubmission::where('status', 'pending')->latest()->get()
            ->map(function ($s) use ($dup) {
                $s->dupes = $dup->find($s->toArray());
                return $s;
            });
        $changes = PendingChange::with('requestedBy')->where('status', 'pending')->latest()->get();
        return view('intake.index', compact('submissions', 'changes'));
    }

    /** Record a CV submission manually (walk-in). The website posts to career.submit. */
    public function store(Request $request)
    {
        $data = $this->validateSubmission($request);
        if ($request->hasFile('cv')) $data['cv_path'] = $request->file('cv')->store('cv-submissions', 'public');
        $data['status'] = 'pending';
        $data['source'] = 'office';
        CvSubmission::create($data);
        return back()->with('status', 'CV submission recorded (pending review).');
    }

    /** PUBLIC endpoint — the goldencareerbd.com/career form posts here. No login. */
    public function publicSubmit(Request $request)
    {
        $data = $this->validateSubmission($request);
        if ($request->hasFile('cv')) $data['cv_path'] = $request->file('cv')->store('cv-submissions', 'public');
        $data['status'] = 'pending';
        $data['source'] = 'website';
        CvSubmission::create($data);
        return response()->json(['ok' => true, 'message' => 'Thank you. Your CV has been received and is pending review.']);
    }

    public function reviewCv(CvSubmission $submission, DuplicateCrewChecker $dup)
    {
        abort_unless($submission->status === 'pending', 404);
        $dupes = $dup->find($submission->toArray());
        return view('intake.review', compact('submission', 'dupes'));
    }

    public function approveCv(Request $request, CvSubmission $submission, DuplicateCrewChecker $dup)
    {
        abort_unless($submission->status === 'pending', 404);
        $matches = $dup->find($submission->toArray());
        if ($matches->isNotEmpty()) {
            return back()->withErrors(['duplicate' => 'Blocked — an existing profile matches this person. Reject instead.']);
        }
        $crew = CrewProfile::create([
            'source' => 'website',
            'gc_id' => CrewProfile::generateGcId(),
            'created_by' => $request->user()->id,
            'name' => $submission->name,
            'mobile' => $submission->mobile,
            'email' => $submission->email,
            'cdc_no' => $submission->cdc_no,
            'passport_no' => $submission->passport_no,
            'coc_no' => $submission->coc_no,
            'sid_no' => $submission->sid_no,
            'nid_no' => $submission->nid_no,
            'birth_registration_no' => $submission->birth_registration_no,
            'date_of_birth' => $submission->date_of_birth,
            'father_name' => $submission->father_name,
            'mother_name' => $submission->mother_name,
            'availability' => 'available',
        ]);
        $submission->update([
            'status' => 'approved', 'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(), 'crew_profile_id' => $crew->id,
        ]);
        return redirect()->route('crew.show', $crew)->with('status', 'Approved — crew profile created ('.$crew->gc_id.').');
    }

    public function rejectCv(Request $request, CvSubmission $submission)
    {
        $submission->update([
            'status' => 'rejected', 'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(), 'notes' => $request->input('notes'),
        ]);
        return redirect()->route('intake.index')->with('status', 'Submission rejected.');
    }

    // ---- Edit approvals ----
    public function approveChange(Request $request, PendingChange $change)
    {
        abort_unless($request->user()->hasAnyRole(['Super Admin', 'Admin']), 403);
        abort_unless($change->status === 'pending', 404);
        $subject = $change->subject();
        if ($subject) {
            $apply = [];
            foreach ($change->changes as $field => $pair) $apply[$field] = $pair['new'] ?? null;
            $subject->update($apply);
        }
        $change->update(['status' => 'approved', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);
        return back()->with('status', 'Edit approved and applied live.');
    }

    public function rejectChange(Request $request, PendingChange $change)
    {
        abort_unless($request->user()->hasAnyRole(['Super Admin', 'Admin']), 403);
        $change->update(['status' => 'rejected', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);
        return back()->with('status', 'Edit rejected.');
    }

    protected function validateSubmission(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'rank_text' => ['nullable', 'string', 'max:120'],
            'mobile' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:191'],
            'cdc_no' => ['nullable', 'string', 'max:120'],
            'passport_no' => ['nullable', 'string', 'max:120'],
            'sid_no' => ['nullable', 'string', 'max:120'],
            'coc_no' => ['nullable', 'string', 'max:120'],
            'nid_no' => ['nullable', 'string', 'max:120'],
            'birth_registration_no' => ['nullable', 'string', 'max:120'],
            'date_of_birth' => ['nullable', 'date'],
            'father_name' => ['nullable', 'string', 'max:191'],
            'mother_name' => ['nullable', 'string', 'max:191'],
            'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:8192'],
        ]);
    }
}
