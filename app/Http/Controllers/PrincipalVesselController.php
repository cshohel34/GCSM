<?php

namespace App\Http\Controllers;

use App\Models\Principal;
use App\Models\PrincipalVessel;
use Illuminate\Http\Request;

class PrincipalVesselController extends Controller
{
    public function store(Request $request, Principal $principal)
    {
        $data = $request->validate([
            'vessel_name' => ['required', 'string', 'max:191'],
            'imo' => ['nullable', 'string', 'max:60'],
            'vessel_type' => ['nullable', 'string', 'max:120'],
            'grt' => ['nullable', 'string', 'max:60'],
            'engine_type' => ['nullable', 'string', 'max:120'],
            'bhp' => ['nullable', 'string', 'max:60'],
            'flag' => ['nullable', 'string', 'max:120'],
            'trading_area' => ['nullable', 'string', 'max:191'],
            'dwt' => ['nullable', 'string', 'max:60'],
        ]);
        $principal->vessels()->create($data);
        return back()->with('status', 'Vessel added.');
    }

    public function destroy(Principal $principal, PrincipalVessel $vessel)
    {
        abort_unless($vessel->principal_id === $principal->id, 404);
        $vessel->delete();
        return back()->with('status', 'Vessel removed.');
    }
}
