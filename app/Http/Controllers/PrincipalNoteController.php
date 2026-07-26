<?php

namespace App\Http\Controllers;

use App\Models\Principal;
use App\Models\PrincipalNote;
use Illuminate\Http\Request;

class PrincipalNoteController extends Controller
{
    public function store(Request $request, Principal $principal)
    {
        $data = $request->validate(['note' => ['required', 'string']]);
        $data['author_id'] = $request->user()->id;
        $principal->companyNotes()->create($data);
        return back()->with('status', 'Note saved.');
    }

    public function destroy(Principal $principal, PrincipalNote $note)
    {
        abort_unless($note->principal_id === $principal->id, 404);
        $note->delete();
        return back()->with('status', 'Note removed.');
    }
}
