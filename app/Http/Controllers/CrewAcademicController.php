<?php

namespace App\Http\Controllers;

use App\Models\CrewAcademic;
use App\Models\CrewProfile;
use Illuminate\Http\Request;

class CrewAcademicController extends Controller
{
    public function store(Request $request, CrewProfile $crew)
    {
        $data = $request->validate([
            'description' => ['required', 'string', 'max:191'],
            'board' => ['nullable', 'string', 'max:191'],
            'group' => ['nullable', 'string', 'max:120'],
            'passing_year' => ['nullable', 'string', 'max:20'],
            'gpa' => ['nullable', 'string', 'max:20'],
        ]);
        $data['source'] = 'manual';
        $row = $crew->academics()->create($data);
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'html' => view('crew.partials.academic_row', ['crew' => $crew, 'row' => $row])->render()]);
        }
        return back()->with('status', 'Educational qualification added.');
    }

    public function destroy(Request $request, CrewProfile $crew, CrewAcademic $academic)
    {
        abort_unless($academic->crew_profile_id === $crew->id, 404);
        $academic->delete();
        if ($request->wantsJson()) return response()->json(['ok' => true]);
        return back()->with('status', 'Educational qualification removed.');
    }
}
