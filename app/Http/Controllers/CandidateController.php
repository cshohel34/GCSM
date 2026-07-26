<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\RequisitionPosition;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CandidateController extends Controller
{
    /** Add a crew to a position's wishlist (CS-03). */
    public function store(Request $request, RequisitionPosition $position)
    {
        $position->loadMissing('requisition');
        if (optional($position->requisition)->deadlinePassed()) {
            return back()->withErrors(['deadline' => 'The requirement deadline has passed — no new crew can be added to this position. A Super Admin can extend the deadline first.']);
        }
        $data = $request->validate([
            'crew_profile_id' => ['required', 'exists:crew_profiles,id'],
        ]);
        $candidate = Candidate::firstOrCreate(
            ['requisition_position_id' => $position->id, 'crew_profile_id' => $data['crew_profile_id']],
            ['stage' => 'wishlisted', 'arranged_by' => $request->user()->id],
        );

        // Build the standard document checklist, auto-mapped from the crew profile.
        app(\App\Services\CandidateChecklist::class)->ensure($candidate);

        return redirect()->route('selection.show', $position->requisition_id)
            ->with('status', 'Added to wishlist — document checklist prepared.');
    }

    /** Move a candidate to a new stage (shortlist / forward / confirm selection). */
    public function stage(Request $request, Candidate $candidate)
    {
        $data = $request->validate([
            'stage' => ['required', Rule::in(['wishlisted', 'shortlisted', 'forwarded', 'final_selected'])],
            'confirmed_at' => ['nullable', 'date', 'required_if:stage,final_selected'],
        ], ['confirmed_at.required_if' => 'Please choose the confirmation date.']);
        $update = ['stage' => $data['stage']];
        if ($data['stage'] === 'forwarded') $update['forwarded_at'] = now();
        if ($data['stage'] === 'final_selected') $update['confirmed_at'] = $data['confirmed_at'];
        $candidate->update($update);
        return back()->with('status', $data['stage'] === 'final_selected'
            ? 'Selection confirmed.'
            : 'Candidate moved to '.Candidate::STAGES[$data['stage']].'.');
    }

    /** Forward the candidate\'s CV + docs to the principal by email, and mark forwarded (CS-05). */
    public function forward(Request $request, Candidate $candidate)
    {
        $candidate->update(['stage' => 'forwarded', 'forwarded_at' => now()]);
        $candidate->loadMissing('crewProfile.currentRank', 'crewProfile.seaServices', 'crewProfile.courses', 'crewProfile.documents', 'position.requisition.principal.contacts');

        $principal = $candidate->position->requisition->principal;
        $to = optional($principal->contacts->firstWhere('is_primary', true))->email
            ?: optional($principal->contacts->first())->email ?: $principal->email;

        if ($to) {
            try {
                $pdf = Pdf::loadView('pdf.cv', ['crew' => $candidate->crewProfile])->output();
                $crewName = $candidate->crewProfile->name;
                Mail::raw("Please find attached the CV of {$crewName} for your review.", function ($m) use ($to, $crewName, $pdf) {
                    $m->to($to)->subject("GCSM — Candidate CV: {$crewName}")
                      ->attachData($pdf, 'CV-'.$crewName.'.pdf', ['mime' => 'application/pdf']);
                });
                return back()->with('status', "Forwarded and emailed to {$to}.");
            } catch (\Throwable $e) {
                return back()->with('status', 'Marked forwarded (email failed: '.$e->getMessage().').');
            }
        }
        return back()->with('status', 'Marked forwarded (no principal email on file).');
    }

    /** Record interview outcome (CS-07). */
    public function interview(Request $request, Candidate $candidate)
    {
        $data = $request->validate([
            'result' => ['required', Rule::in([
                'rejected_by_company', 'interview_selected', 'interview_passed', 'interview_failed',
            ])],
            'interview_date' => ['nullable', 'date'],
            // A reason is mandatory when the company rejects the CV or the interview is failed.
            'fail_reason' => [
                'nullable',
                'required_if:result,interview_failed',
                'required_if:result,rejected_by_company',
                'string', 'max:500',
            ],
        ], [
            'fail_reason.required_if' => 'Please write the reason.',
        ]);

        $rejected = $data['result'] === 'rejected_by_company';
        $needsReason = in_array($data['result'], ['interview_failed', 'rejected_by_company'], true);

        $candidate->update([
            'stage' => $data['result'],
            // A rejected CV never reached an interview. Otherwise keep whatever date was
            // given, or leave the existing one untouched — never invent one.
            'interview_date' => $rejected ? null : ($data['interview_date'] ?? $candidate->interview_date),
            'fail_reason' => $needsReason ? $data['fail_reason'] : null,
        ]);

        return back()->with('status', $rejected
            ? 'Candidate marked as rejected by the company.'
            : 'Interview outcome recorded.');
    }

    /**
     * Record the service-charge decision for a candidate.
     *  - Yes: an amount is captured and a DRAFT accounting journal is auto-prepared
     *         (Dr Service Charge Receivable / Cr Service Charge Income), party = crew.
     *  - No:  a written reason is mandatory.
     */
    public function serviceCharge(Request $request, Candidate $candidate)
    {
        $data = $request->validate([
            'decision' => ['required', Rule::in(['yes', 'no'])],
            'amount' => ['nullable', 'required_if:decision,yes', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'required_if:decision,no', 'string', 'max:500'],
        ], [
            'amount.required_if' => 'Enter the service charge amount.',
            'reason.required_if' => 'Please write why there is no service charge.',
        ]);

        if ($data['decision'] === 'no') {
            // Remove any previously drafted journal.
            if ($candidate->service_charge_txn_id) {
                optional(\App\Models\Transaction::find($candidate->service_charge_txn_id))->delete();
            }
            $candidate->update([
                'service_charge' => null,
                'service_charge_decided' => true,
                'service_charge_received' => false,
                'no_charge_reason' => $data['reason'],
                'service_charge_txn_id' => null,
            ]);
            return back()->with('status', 'Recorded: no service charge for this candidate.');
        }

        // decision = yes
        $amount = round((float) $data['amount'], 2);
        $txnId = $this->draftServiceChargeJournal($candidate, $amount, $request->user());
        $candidate->update([
            'service_charge' => $amount,
            'service_charge_decided' => true,
            'no_charge_reason' => null,
            'service_charge_txn_id' => $txnId,
        ]);

        return back()->with('status', $txnId
            ? 'Service charge saved and drafted to the accounting journal.'
            : 'Service charge saved. (Accounting chart not found — journal draft skipped.)');
    }

    /**
     * Create/replace a DRAFT journal voucher for the service charge. Returns the txn id
     * (or null if the chart of accounts is missing). Never throws — a bookkeeping hiccup
     * must not block the selection workflow.
     */
    protected function draftServiceChargeJournal(Candidate $candidate, float $amount, $user): ?int
    {
        try {
            $receivable = \App\Models\Account::where('code', '1230')->first(); // Service Charge Receivable
            $income     = \App\Models\Account::where('code', '4020')->first(); // Service Charge Income
            if (! $receivable || ! $income) return null;

            // Remove any earlier draft for this candidate so amounts never stack up.
            if ($candidate->service_charge_txn_id) {
                optional(\App\Models\Transaction::find($candidate->service_charge_txn_id))->delete();
            }

            $candidate->loadMissing('crewProfile', 'position.requisition.principal', 'position.rank');
            $crew = $candidate->crewProfile;
            $ref  = optional($candidate->position->requisition)->reference ?: ('REQ-'.optional($candidate->position)->requisition_id);
            $memo = 'Service charge — '.optional($crew)->name.' ('.$ref.')';

            $txn = app(PostingService::class)->record(
                [
                    'voucher_type' => 'journal',
                    'status' => 'draft',
                    'date' => now()->toDateString(),
                    'reference' => $ref,
                    'narration' => 'Service charge for placement of '.optional($crew)->name
                        .' — '.optional(optional($candidate->position->requisition)->principal)->name,
                    'source_type' => \App\Models\Candidate::class,
                    'source_id' => $candidate->id,
                    'created_by' => optional($user)->id,
                ],
                [
                    ['account_id' => $receivable->id, 'debit' => $amount, 'credit' => 0,
                        'party_type' => \App\Models\CrewProfile::class, 'party_id' => optional($crew)->id, 'memo' => $memo],
                    ['account_id' => $income->id, 'debit' => 0, 'credit' => $amount, 'memo' => $memo],
                ]
            );
            return $txn->id;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Sign the candidate on (CS-09/10): requires interview passed + documents complete
     * + service charge received. Creates a placement (onboard) that Module 3 shows.
     */
    public function signOn(Request $request, Candidate $candidate)
    {
        $data = $request->validate([
            'sign_on_date' => ['required', 'date'],
            'expected_joining_date' => ['nullable', 'date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'place_of_joining' => ['nullable', 'string', 'max:191'],
        ]);

        if (! in_array($candidate->stage, ['interview_passed', 'final_selected'])) {
            throw ValidationException::withMessages(['stage' => 'Candidate must pass the interview / be confirmed before sign-on.']);
        }
        if (! $candidate->service_charge_decided) {
            throw ValidationException::withMessages(['stage' => 'Record the service-charge decision (Yes with amount, or No with a reason) before sign-on.']);
        }

        $candidate->loadMissing('position.requisition', 'position.vessel', 'position.rank');
        $position = $candidate->position;

        $placement = \App\Models\Placement::create([
            'crew_profile_id' => $candidate->crew_profile_id,
            'principal_id' => $position->requisition->principal_id,
            'principal_vessel_id' => $position->principal_vessel_id,
            'rank' => optional($position->rank)->rank_name,
            'sign_on_date' => $data['sign_on_date'],
            'expected_joining_date' => $data['expected_joining_date'] ?? null,
            'place_of_joining' => $data['place_of_joining'] ?? null,
            'monthly_salary_usd' => $data['salary'] ?? null,
            'status' => 'onboard',
            'arranged_by' => $request->user()->id,
            'service_charge' => $candidate->service_charge,
        ]);

        $candidate->update(['stage' => 'signed_on', 'placement_id' => $placement->id]);

        // Crew is now onboard — reflect it on the profile and clear any placement urgency.
        \App\Models\CrewProfile::whereKey($candidate->crew_profile_id)->update([
            'availability' => 'onboard',
            'job_urgency' => 'normal',
            'job_deadline' => null,
            'available_from' => null,
        ]);

        // Mark position filled when headcount reached.
        $signedOn = $position->candidates()->where('stage', 'signed_on')->count();
        if ($signedOn >= $position->headcount) {
            $position->update(['status' => 'filled']);
        }

        return back()->with('status', 'Crew signed on and placed onboard.');
    }

    /** Sign the crew off from the Crew Selection module (uses the same shared logic). */
    public function signOffCandidate(Request $request, Candidate $candidate)
    {
        $placement = $candidate->placement;
        if (! $placement || $placement->status !== 'onboard') {
            throw ValidationException::withMessages(['stage' => 'This crew is not currently onboard.']);
        }

        $data = $request->validate([
            'sign_off_date' => ['required', 'date', 'after_or_equal:sign_on_date'],
            'has_dues' => ['nullable', 'boolean'],
            'availability' => ['nullable', \Illuminate\Validation\Rule::in(['available','not_available','resting'])],
            'available_from' => ['nullable', 'date'],
            'job_urgency' => ['nullable', \Illuminate\Validation\Rule::in(['normal','high','urgent'])],
            'job_deadline' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:191'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        app(\App\Services\SignOffService::class)->apply($placement, $data, $request->user()->id);

        return back()->with('status', 'Crew signed off; voyage added to sea service and profile updated.');
    }

    public function destroy(Candidate $candidate)
    {
        $reqId = $candidate->position->requisition_id;
        $candidate->delete();
        return redirect()->route('selection.show', $reqId)->with('status', 'Candidate removed.');
    }

    // ---- Document checklist (CS-08) ----
    public function addChecklistItem(Request $request, Candidate $candidate)
    {
        $data = $request->validate([
            'item' => ['required', 'string', 'max:191'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);
        $next = (int) $candidate->checklistItems()->max('sort_order') + 1;
        $candidate->checklistItems()->create([
            'item' => $data['item'],
            'notes' => $data['notes'] ?? null,
            'sort_order' => $next,
            'required' => true,
        ]);
        return back()->with('status', 'Checklist item added.');
    }

    /** Set a checklist item's status (Yes/No). Auto-mapped items are profile-driven and locked. */
    public function setChecklistStatus(Request $request, \App\Models\CandidateChecklistItem $item)
    {
        if ($item->isAutoMapped()) {
            return back()->with('status', 'This item is mapped automatically from the crew profile and cannot be changed here.');
        }
        $data = $request->validate(['is_received' => ['required', 'boolean']]);
        $item->update(['is_received' => (bool) $data['is_received']]);
        return back()->with('status', 'Checklist updated.');
    }

    /** Save a remark against a checklist item (recorded in the audit trail with who/when). */
    public function remarkChecklistItem(Request $request, \App\Models\CandidateChecklistItem $item)
    {
        $data = $request->validate(['notes' => ['nullable', 'string', 'max:255']]);
        $item->update([
            'notes' => $data['notes'] ?? null,
            'remark_by' => $request->user()->id,
            'remark_at' => now(),
        ]);
        return back()->with('status', 'Remark saved.');
    }

    /** Remove a checklist item (custom items only — standard items are kept). */
    public function destroyChecklistItem(\App\Models\CandidateChecklistItem $item)
    {
        if ($item->code) {
            return back()->with('status', 'Standard checklist items cannot be removed.');
        }
        $item->delete();
        return back()->with('status', 'Checklist item removed.');
    }

    /**
     * Issue / download the GCSM Sign-On Letter (Department of Shipping request) as a PDF
     * on the company letterhead. The first download registers the letter with an
     * auto-generated, immutable reference number and issue date; later downloads reuse it.
     */
    public function signOnLetter(Request $request, Candidate $candidate)
    {
        $candidate->load([
            'crewProfile.currentRank', 'crewProfile.documents',
            'position.rank', 'position.vessel',
            'position.requisition.principal', 'placement',
        ]);
        $crew = $candidate->crewProfile;
        $placement = $candidate->placement;

        $passportDoc = optional($crew)->documents
            ? $crew->documents->first(fn ($d) => str_contains(mb_strtolower((string) $d->doc_type), 'passport'))
            : null;
        $vessel = optional($candidate->position)->vessel;
        $principal = optional(optional($candidate->position)->requisition)->principal;
        $rank = optional(optional($candidate->position)->rank)->rank_name ?: optional(optional($crew)->currentRank)->rank_name;

        // Register the letter once per candidate, capturing an immutable ref + issue date.
        $letter = \App\Models\SignOnLetter::where('candidate_id', $candidate->id)->first();
        if (! $letter) {
            $year = (int) now()->format('Y');
            $nextNo = (int) \App\Models\SignOnLetter::where('letter_year', $year)->max('letter_no') + 1;
            $letter = \App\Models\SignOnLetter::create([
                'reference_no' => 'GCSM/Crew/SignOn/'.$year.'/'.str_pad((string) $nextNo, 4, '0', STR_PAD_LEFT),
                'letter_no' => $nextNo,
                'letter_year' => $year,
                'letter_date' => now()->toDateString(),
                'candidate_id' => $candidate->id,
                'crew_profile_id' => optional($crew)->id,
                'principal_id' => optional($principal)->id,
                'principal_vessel_id' => optional($vessel)->id,
                'crew_name' => optional($crew)->name,
                'cdc_no' => optional($crew)->cdc_no,
                'passport_no' => optional($crew)->passport_no,
                'mobile' => optional($crew)->mobile,
                'rank' => $rank,
                'vessel_name' => optional($vessel)->vessel_name,
                'company_name' => optional($principal)->name,
                'joining_date' => optional($placement)->expected_joining_date ?: optional($placement)->sign_on_date,
                'salary' => optional($placement)->monthly_salary_usd ? number_format((float) $placement->monthly_salary_usd, 0) : null,
                'place_of_joining' => optional($placement)->place_of_joining,
                'passport_issue' => optional($passportDoc)->issue_date,
                'issued_by' => optional($request->user())->id,
            ]);
        }

        // Live values from the current placement (the source of truth for the letter body).
        $liveJoining  = optional($placement)->expected_joining_date ?: optional($placement)->sign_on_date;
        $liveSalary   = optional($placement)->monthly_salary_usd ? number_format((float) $placement->monthly_salary_usd, 0) : null;
        $livePlace    = optional($placement)->place_of_joining;
        $livePassport = optional($passportDoc)->issue_date;

        // Back-fill any snapshot columns left empty by an earlier (pre sign-on) download,
        // without ever touching the immutable reference number or issue date.
        $backfill = [];
        if (! $letter->joining_date && $liveJoining)   $backfill['joining_date'] = $liveJoining;
        if (! $letter->salary && $liveSalary)          $backfill['salary'] = $liveSalary;
        if (! $letter->place_of_joining && $livePlace) $backfill['place_of_joining'] = $livePlace;
        if (! $letter->passport_issue && $livePassport) $backfill['passport_issue'] = $livePassport;
        if ($backfill) { $letter->forceFill($backfill)->save(); }

        $L = [
            'crew' => $crew,
            'position' => $candidate->position,
            'vessel' => $vessel,
            'principal' => $principal,
            'ref' => $letter->reference_no,
            'letterDate' => $letter->letter_date->format('d.m.Y'),
            'joiningDate' => optional($letter->joining_date ?: $liveJoining)->format('d-M-Y') ?: '',
            'salary' => $letter->salary ?: ($liveSalary ?: ''),
            'placeOfJoining' => $letter->place_of_joining ?: ($livePlace ?: ''),
            'passportIssue' => optional($letter->passport_issue ?: $livePassport)->format('d-M-Y') ?: '',
        ];

        $filename = 'GCSM-SignOn-'.(optional($crew)->display_id ?: $candidate->id);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.signon_letter', $L)
            ->setPaper('a4', 'portrait')
            ->download($filename.'.pdf');
    }

    /** Re-map the checklist against the crew profile's current documents & certificates. */
    public function remapChecklist(Candidate $candidate, \App\Services\CandidateChecklist $checklist)
    {
        $checklist->remap($candidate);
        return back()->with('status', 'Checklist re-mapped from the crew profile.');
    }

    /** Download the crew's document checklist on the GCSM official letterhead. */
    public function checklistPdf(Candidate $candidate, \App\Services\CandidateChecklist $checklist)
    {
        $checklist->ensure($candidate);
        $candidate->load([
            'crewProfile.currentRank',
            'position.requisition.principal',
            'position.vessel',
            'checklistItems' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.candidate_checklist', [
            'candidate' => $candidate,
            'crew' => $candidate->crewProfile,
            'items' => $candidate->checklistItems,
        ])->setPaper('a4', 'portrait');

        $ref = optional($candidate->crewProfile)->display_id ?: $candidate->id;
        return $pdf->download('GCSM-Checklist-'.$ref.'.pdf');
    }
}
