<?php

namespace App\Http\Controllers;

use App\Models\CrewProfile;
use App\Models\CrewOffence;
use Illuminate\Http\Request;

class CrewOffenceController extends Controller
{

    public function store(Request $request, CrewProfile $crew)
    {
        $data = $request->validate([
            'offence_date' => ['nullable', 'date'],
            'description' => ['required', 'string'],
            'source' => ['nullable', 'string', 'max:191'],
            'action_taken' => ['nullable', 'string', 'max:191'],
        ]);
        $data['recorded_by'] = $request->user()->id;
        $crew->offences()->create($data);
        return back()->with('status', 'Offence recorded.');
    }

    public function destroy(CrewProfile $crew, CrewOffence $offence)
    {
        abort_unless($offence->crew_profile_id === $crew->id, 404);
        $offence->delete();
        return back()->with('status', 'Offence removed.');
    }
}
