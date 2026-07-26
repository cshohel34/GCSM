<?php

namespace App\Http\Controllers;

use App\Models\CrewMaritimeEducation;
use App\Models\CrewProfile;
use Illuminate\Http\Request;

class CrewMaritimeEducationController extends Controller
{
    public function store(Request $request, CrewProfile $crew)
    {
        $data = $request->validate([
            'institute' => ['required', 'string', 'max:191'],
            'department' => ['nullable', 'string', 'max:191'],
            'year_of_graduation' => ['nullable', 'string', 'max:20'],
        ]);
        $data['source'] = 'manual';
        $row = $crew->maritimeEducations()->create($data);
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'html' => view('crew.partials.maritime_row', ['crew' => $crew, 'row' => $row])->render()]);
        }
        return back()->with('status', 'Maritime education added.');
    }

    public function destroy(Request $request, CrewProfile $crew, CrewMaritimeEducation $education)
    {
        abort_unless($education->crew_profile_id === $crew->id, 404);
        $education->delete();
        if ($request->wantsJson()) return response()->json(['ok' => true]);
        return back()->with('status', 'Maritime education removed.');
    }
}
