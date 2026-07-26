<?php

namespace App\Http\Controllers;

use App\Models\CrewNote;
use App\Models\CrewProfile;
use Illuminate\Http\Request;

class CrewNoteController extends Controller
{

    public function store(Request $request, CrewProfile $crew)
    {
        $data = $request->validate(['note' => ['required', 'string']]);
        $data['author_id'] = $request->user()->id;
        $crew->notes()->create($data);
        return back()->with('status', 'Note saved.');
    }

    public function destroy(CrewProfile $crew, CrewNote $note)
    {
        abort_unless($note->crew_profile_id === $crew->id, 404);
        $note->delete();
        return back()->with('status', 'Note removed.');
    }
}
