<?php

namespace App\Http\Controllers;

use App\Models\CrewDocument;
use App\Models\SignOnLetter;
use App\Exports\AccountingReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class DocumentDashboardController extends Controller
{
    /** Register of every Sign-On Letter issued by the system (searchable). */
    public function signOnRegister(Request $request)
    {
        $filters = $request->only([
            'crew', 'cdc_no', 'passport_no', 'mobile', 'vessel', 'company', 'rank',
            'reference', 'joining_date', 'date_from', 'date_to',
        ]);

        $letters = SignOnLetter::with(['issuedBy', 'crewProfile'])
            ->search($filters)
            ->orderByDesc('letter_date')->orderByDesc('id')
            ->paginate(25)->withQueryString();

        return view('document.signon_register', [
            'letters' => $letters,
            'filters' => $filters,
        ]);
    }

    public function index(Request $request)
    {
        $within = (int) $request->get('within', 0); // expiring within N days
        $docs = CrewDocument::query()
            ->with('crewProfile')
            ->when($request->get('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->get('doc_type'), fn ($q, $v) => $q->where('doc_type', 'like', "%{$v}%"))
            ->when($request->get('crew'), fn ($q, $v) => $q->whereHas('crewProfile',
                fn ($q) => $q->where('name', 'like', "%{$v}%")))
            ->when($within > 0, fn ($q) => $q->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays($within)->toDateString()]))
            ->orderBy('expiry_date')
            ->paginate(30)->withQueryString();

        $counts = [
            'valid' => CrewDocument::where('status', 'valid')->count(),
            'expiring' => CrewDocument::where('status', 'expiring')->count(),
            'expired' => CrewDocument::where('status', 'expired')->count(),
        ];

        return view('document.index', [
            'docs' => $docs,
            'counts' => $counts,
            'filters' => $request->only(['status', 'doc_type', 'crew', 'within']),
        ]);
    }

    public function export(Request $request)
    {
        $docs = CrewDocument::with('crewProfile')
            ->when($request->get('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->get('doc_type'), fn ($q, $v) => $q->where('doc_type', 'like', "%{$v}%"))
            ->orderBy('expiry_date')->get();
        $columns = ['Crew', 'Document', 'Number', 'Issue', 'Expiry', 'Status'];
        $rows = [];
        foreach ($docs as $d) {
            $rows[] = [optional($d->crewProfile)->name, $d->doc_type, $d->number,
                optional($d->issue_date)->toDateString(), optional($d->expiry_date)->toDateString(), ucfirst($d->status)];
        }
        $title = 'Crew Documents'; $meta = ['Generated' => now()->toDateString(), 'Count' => count($rows)];
        if ($request->get('export') === 'excel') {
            return Excel::download(new AccountingReportExport($title, $meta, $columns, $rows), 'Crew-Documents-'.now()->format('Ymd').'.xlsx');
        }
        return Pdf::loadView('pdf.report', ['title'=>$title,'meta'=>$meta,'columns'=>$columns,'rows'=>$rows,'numeric'=>[]])->setPaper('a4','landscape')->download('Crew-Documents-'.now()->format('Ymd').'.pdf');
    }

    /** Validity-change history for one document (DM-03) via the audit log. */
    public function history(CrewDocument $document)
    {
        $document->load('crewProfile');
        $audits = $document->audits()->with('user')->latest()->get();
        return view('document.history', ['document' => $document, 'audits' => $audits]);
    }
}
