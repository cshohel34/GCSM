<?php

namespace App\Http\Controllers;

use App\Models\Principal;
use App\Models\PrincipalOffence;
use Illuminate\Http\Request;

class PrincipalOffenceController extends Controller
{
    public function store(Request $request, Principal $principal)
    {
        $data = $request->validate([
            'offence_date' => ['nullable', 'date'],
            'description' => ['required', 'string'],
            'source' => ['nullable', 'string', 'max:191'],
            'action_taken' => ['nullable', 'string', 'max:191'],
        ]);
        $data['recorded_by'] = $request->user()->id;
        $principal->offences()->create($data);
        return back()->with('status', 'Offence recorded.');
    }

    public function destroy(Principal $principal, PrincipalOffence $offence)
    {
        abort_unless($offence->principal_id === $principal->id, 404);
        $offence->delete();
        return back()->with('status', 'Offence removed.');
    }
}
