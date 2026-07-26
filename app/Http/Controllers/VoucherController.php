<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionLine;
use App\Services\PostingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $vouchers = Transaction::with('lines')
            ->when($request->get('type'), fn ($q, $v) => $q->where('voucher_type', $v))
            ->when($request->get('from'), fn ($q, $v) => $q->whereDate('date', '>=', $v))
            ->when($request->get('to'), fn ($q, $v) => $q->whereDate('date', '<=', $v))
            ->when($request->get('q'), fn ($q, $v) => $q->where('voucher_no', 'like', "%{$v}%")->orWhere('narration', 'like', "%{$v}%"))
            ->latest('date')->latest('id')
            ->paginate(25)->withQueryString();
        return view('accounting.vouchers.index', ['vouchers' => $vouchers, 'filters' => $request->all()]);
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'journal');
        return view('accounting.vouchers.form', [
            'type' => in_array($type, ['receipt', 'payment', 'journal', 'contra']) ? $type : 'journal',
            'accounts' => Account::where('is_group', false)->where('active', true)->orderBy('code')->get(),
            'parties' => TransactionLine::partyOptions(),
        ]);
    }

    public function store(Request $request, PostingService $posting)
    {
        $data = $request->validate([
            'voucher_type' => ['required', Rule::in(['receipt', 'payment', 'journal', 'contra'])],
            'date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:120'],
            'narration' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'exists:accounts,id'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.party_type' => ['nullable', 'string'],
            'lines.*.party_id' => ['nullable'],
            'lines.*.memo' => ['nullable', 'string', 'max:191'],
        ]);

        $txn = $posting->record([
            'voucher_type' => $data['voucher_type'],
            'date' => $data['date'],
            'reference' => $data['reference'] ?? null,
            'narration' => $data['narration'] ?? null,
            'created_by' => $request->user()->id,
        ], $data['lines']);

        return redirect()->route('accounting.vouchers.show', $txn)->with('status', 'Voucher '.$txn->voucher_no.' posted.');
    }

    public function show(Transaction $voucher)
    {
        $voucher->load('lines.account', 'createdBy');
        return view('accounting.vouchers.show', ['voucher' => $voucher]);
    }

    public function void(Transaction $voucher)
    {
        abort_unless(auth()->user()->can('accounting.post'), 403);
        $voucher->update(['status' => 'void']);
        return back()->with('status', 'Voucher voided.');
    }
}
