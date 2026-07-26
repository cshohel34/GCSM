<?php

namespace App\Http\Controllers;

use App\Models\Principal;
use App\Models\PrincipalDocument;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PrincipalDocumentController extends Controller
{
    public function store(Request $request, Principal $principal)
    {
        $data = $request->validate([
            'doc_type' => ['required', Rule::in(['contract', 'other'])],
            'title' => ['required', 'string', 'max:191'],
            'signed_date' => ['nullable', 'date'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'notes' => ['nullable', 'string'],
        ]);
        $data['file_path'] = $request->file('file')->store('principals/documents', 'public');
        unset($data['file']);
        $principal->documents()->create($data);
        return back()->with('status', 'Document uploaded.');
    }

    public function destroy(Principal $principal, PrincipalDocument $document)
    {
        abort_unless($document->principal_id === $principal->id, 404);
        $document->delete();
        // If the last contract is removed, deactivate the company (PM-02).
        if (! $principal->fresh()->hasContract()) {
            $principal->update(['status' => 'inactive']);
        }
        return back()->with('status', 'Document removed.');
    }
}
