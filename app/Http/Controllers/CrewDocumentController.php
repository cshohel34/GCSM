<?php

namespace App\Http\Controllers;

use App\Models\CrewDocument;
use App\Models\CrewProfile;
use Illuminate\Http\Request;

class CrewDocumentController extends Controller
{

    public function store(Request $request, CrewProfile $crew)
    {
        $data = $request->validate([
            'doc_type' => ['required', 'string', 'max:120'],
            'number' => ['nullable', 'string', 'max:120'],
            'grade' => ['nullable', 'string', 'max:120'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'place_of_issue' => ['nullable', 'string', 'max:120'],
            'issuing_authority' => ['nullable', 'string', 'max:191'],
            'scan' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:8192'],
        ]);
        if ($request->hasFile('scan')) {
            $data['scan_path'] = $request->file('scan')->store('crew/documents', 'public');
        }
        unset($data['scan']);
        $row = $crew->documents()->create($data); // status auto-set by observer
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'html' => view('crew.partials.document_row', ['crew' => $crew, 'row' => $row->fresh()])->render()]);
        }
        return back()->with('status', 'Document added.');
    }

    public function update(Request $request, CrewProfile $crew, CrewDocument $document)
    {
        abort_unless($document->crew_profile_id === $crew->id, 404);
        $data = $request->validate([
            'doc_type' => ['required', 'string', 'max:120'],
            'number' => ['nullable', 'string', 'max:120'],
            'grade' => ['nullable', 'string', 'max:120'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'place_of_issue' => ['nullable', 'string', 'max:120'],
            'issuing_authority' => ['nullable', 'string', 'max:191'],
            'scan' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:8192'],
        ]);
        if ($request->hasFile('scan')) {
            $data['scan_path'] = $request->file('scan')->store('crew/documents', 'public');
        }
        unset($data['scan']);
        $document->update($data);
        return back()->with('status', 'Document updated.');
    }

    public function destroy(Request $request, CrewProfile $crew, CrewDocument $document)
    {
        abort_unless($document->crew_profile_id === $crew->id, 404);
        $document->delete();
        if ($request->wantsJson()) return response()->json(['ok' => true]);
        return back()->with('status', 'Document removed.');
    }
}
