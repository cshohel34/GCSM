<?php

namespace App\Http\Controllers;

use App\Models\BusinessDocument;
use Illuminate\Http\Request;

class BusinessDocumentController extends Controller
{
    public function index(Request $request)
    {
        $docs = BusinessDocument::query()
            ->when($request->get('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->get('q'), fn ($q, $v) => $q->where('title', 'like', "%{$v}%"))
            ->orderBy('expiry_date')->paginate(20)->withQueryString();
        return view('document.business_index', ['docs' => $docs, 'filters' => $request->only(['status', 'q'])]);
    }

    public function create() { return view('document.business_form', ['doc' => new BusinessDocument()]); }

    public function store(Request $request)
    {
        $d = BusinessDocument::create($this->validated($request));
        $this->scan($request, $d);
        return redirect()->route('document.business.index')->with('status', 'Business document added.');
    }

    public function edit(BusinessDocument $document) { return view('document.business_form', ['doc' => $document]); }

    public function update(Request $request, BusinessDocument $document)
    {
        $document->update($this->validated($request));
        $this->scan($request, $document);
        return redirect()->route('document.business.index')->with('status', 'Updated.');
    }

    public function destroy(BusinessDocument $document)
    {
        $document->delete();
        return redirect()->route('document.business.index')->with('status', 'Removed.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'category' => ['nullable', 'string', 'max:120'],
            'number' => ['nullable', 'string', 'max:120'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    protected function scan(Request $request, BusinessDocument $d): void
    {
        $request->validate(['scan' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240']]);
        if ($request->hasFile('scan')) $d->update(['scan_path' => $request->file('scan')->store('business-docs', 'public')]);
    }
}
