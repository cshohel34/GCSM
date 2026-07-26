<?php

namespace App\Http\Controllers;

use App\Models\CrewProfile;
use App\Models\Placement;
use App\Models\Principal;
use Illuminate\Http\Request;

class PlacementController extends Controller
{
    public function store(Request $request, Principal $principal)
    {
        $data = $request->validate([
            'crew_profile_id' => ['required', 'exists:crew_profiles,id'],
            'principal_vessel_id' => ['nullable', 'exists:principal_vessels,id'],
            'rank' => ['nullable', 'string', 'max:120'],
            'sign_on_date' => ['nullable', 'date'],
            'service_charge' => ['nullable', 'numeric', 'min:0'],
        ]);
        $data['status'] = 'onboard';
        $data['arranged_by'] = $request->user()->id;
        $principal->placements()->create($data);

        // Reflect onboard status on the crew profile.
        CrewProfile::whereKey($data['crew_profile_id'])->update([
            'availability' => 'onboard',
            'job_urgency' => 'normal',
            'job_deadline' => null,
            'available_from' => null,
        ]);

        return back()->with('status', 'Crew placed onboard.');
    }

    /** Sign a crew off from within a principal (Module 3). */
    public function signOff(Request $request, Principal $principal, Placement $placement)
    {
        abort_unless($placement->principal_id === $principal->id, 404);
        $this->applySignOff($request, $placement);
        return back()->with('status', 'Crew signed off; voyage completed, CV & availability updated.');
    }

    /** Sign a crew off from anywhere the voyage is reachable (crew profile, salary, etc.). */
    public function signOffPlacement(Request $request, Placement $placement)
    {
        $this->applySignOff($request, $placement);
        return back()->with('status', 'Crew signed off; voyage completed, CV & availability updated.');
    }

    /**
     * Complete a voyage: close the placement, write a sea-service row so the CV
     * auto-updates, and reset the crew's availability (Available or Resting).
     */
    protected function applySignOff(Request $request, Placement $placement): void
    {
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
    }
}