<?php

namespace App\Http\Controllers;

use App\Exports\AccountingReportExport;
use App\Models\Account;
use App\Models\CrewProfile;
use App\Models\Principal;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionLine;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Services\LedgerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AccountingReportController extends Controller
{
    public function __construct(protected LedgerService $ledger) {}

    public function dashboard()
    {
        $from = now()->startOfMonth()->toDateString();
        $to = now()->toDateString();
        $income = $this->ledger->classTotal('income', $from, $to);
        $expense = $this->ledger->classTotal('expense', $from, $to);
        $cashBank = [];
        foreach (Account::where('is_cash_bank', true)->orderBy('code')->get() as $a) {
            $cashBank[] = ['account' => $a, 'balance' => $this->ledger->balance($a, $to)];
        }
        return view('accounting.dashboard', [
            'income' => $income, 'expense' => $expense, 'profit' => round($income - $expense, 2),
            'cashBank' => $cashBank, 'from' => $from, 'to' => $to,
        ]);
    }

    public function trialBalance(Request $request)
    {
        [$from, $to] = $this->range($request);
        $rows = $this->ledger->trialBalance($from, $to);
        if ($request->filled('export')) {
            $data = [];
            foreach ($rows as $r) $data[] = [$r['account']->code, $r['account']->name, $this->n($r['debit']), $this->n($r['credit'])];
            $data[] = ['', 'TOTAL', $this->n(collect($rows)->sum('debit')), $this->n(collect($rows)->sum('credit')), '_total' => true];
            return $this->export($request, 'Trial Balance', ['As of' => $to], ['Code', 'Account', 'Debit', 'Credit'], $data, [2, 3]);
        }
        return view('accounting.reports.trial_balance', compact('rows', 'from', 'to'));
    }

    public function ledger(Request $request)
    {
        [$from, $to] = $this->range($request);
        $account = $request->get('account_id') ? Account::find($request->get('account_id')) : null;
        $data = $account ? $this->ledger->generalLedger($account, $from, $to) : null;
        if ($request->filled('export') && $data) {
            $rows = [['', '', 'Opening', '', '', $this->n($data['opening'])]];
            foreach ($data['rows'] as $r) $rows[] = [$r['line']->tdate, $r['line']->voucher_no, $r['line']->narration, $this->n($r['line']->debit), $this->n($r['line']->credit), $this->n($r['running'])];
            $rows[] = ['', '', 'Closing', '', '', $this->n($data['closing']), '_total' => true];
            return $this->export($request, 'General Ledger — '.$account->name, ['Account' => $account->code.' '.$account->name, 'From' => $from, 'To' => $to],
                ['Date', 'Voucher', 'Narration', 'Debit', 'Credit', 'Balance'], $rows, [3, 4, 5]);
        }
        return view('accounting.reports.ledger', [
            'accounts' => Account::where('is_group', false)->orderBy('code')->get(),
            'account' => $account, 'data' => $data, 'from' => $from, 'to' => $to,
        ]);
    }

    public function dayBook(Request $request)
    {
        [$from, $to] = $this->range($request);
        $vouchers = Transaction::with('lines.account')
            ->where('status', 'posted')->whereBetween('date', [$from, $to])
            ->orderBy('date')->orderBy('id')->get();
        if ($request->filled('export')) {
            $rows = [];
            foreach ($vouchers as $v) {
                foreach ($v->lines as $l) {
                    $rows[] = [$v->date->toDateString(), $v->voucher_no, ucfirst($v->voucher_type),
                        $l->account->code.' '.$l->account->name, $l->partyName(), $this->n($l->debit), $this->n($l->credit)];
                }
            }
            return $this->export($request, 'Day Book', ['From' => $from, 'To' => $to],
                ['Date', 'Voucher', 'Type', 'Account', 'Party', 'Debit', 'Credit'], $rows, [5, 6]);
        }
        return view('accounting.reports.day_book', compact('vouchers', 'from', 'to'));
    }

    public function cashBank(Request $request)
    {
        [$from, $to] = $this->range($request);
        $accounts = Account::where('is_cash_bank', true)->orderBy('code')->get();
        $account = $request->get('account_id') ? Account::find($request->get('account_id')) : $accounts->first();
        $data = $account ? $this->ledger->generalLedger($account, $from, $to) : null;
        if ($request->filled('export') && $data) {
            $rows = [['', '', 'Opening', '', '', $this->n($data['opening'])]];
            foreach ($data['rows'] as $r) $rows[] = [$r['line']->tdate, $r['line']->voucher_no, $r['line']->narration, $this->n($r['line']->debit), $this->n($r['line']->credit), $this->n($r['running'])];
            $rows[] = ['', '', 'Closing', '', '', $this->n($data['closing']), '_total' => true];
            return $this->export($request, 'Cash-Bank Book — '.$account->name, ['Account' => $account->name.' ['.$account->currency.']', 'From' => $from, 'To' => $to],
                ['Date', 'Voucher', 'Narration', 'In (Dr)', 'Out (Cr)', 'Balance'], $rows, [3, 4, 5]);
        }
        return view('accounting.reports.cash_bank', compact('accounts', 'account', 'data', 'from', 'to'));
    }

    public function profitLoss(Request $request)
    {
        [$from, $to] = $this->range($request);
        $income = $this->ledger->accountsByType('income', $from, $to, true);
        $expense = $this->ledger->accountsByType('expense', $from, $to, false);
        $totalIncome = round(array_sum(array_column($income, 'amount')), 2);
        $totalExpense = round(array_sum(array_column($expense, 'amount')), 2);
        if ($request->filled('export')) {
            $rows = [];
            foreach ($income as $r) $rows[] = ['Income', $r['account']->name, $this->n($r['amount'])];
            $rows[] = ['', 'Total Income', $this->n($totalIncome), '_total' => true];
            foreach ($expense as $r) $rows[] = ['Expense', $r['account']->name, $this->n($r['amount'])];
            $rows[] = ['', 'Total Expense', $this->n($totalExpense), '_total' => true];
            $rows[] = ['', 'NET '.(($totalIncome - $totalExpense) >= 0 ? 'PROFIT' : 'LOSS'), $this->n($totalIncome - $totalExpense), '_total' => true];
            return $this->export($request, 'Profit and Loss', ['From' => $from, 'To' => $to], ['Section', 'Account', 'Amount'], $rows, [2]);
        }
        return view('accounting.reports.profit_loss', compact('income', 'expense', 'totalIncome', 'totalExpense', 'from', 'to'));
    }

    public function balanceSheet(Request $request)
    {
        $to = $request->get('to', now()->toDateString());
        $assets = $this->typeBalances('asset', $to);
        $liabilities = $this->typeBalances('liability', $to, true);
        $equity = $this->typeBalances('equity', $to, true);
        $totalAssets = round(array_sum(array_column($assets, 'amount')), 2);
        $currentProfit = round($this->ledger->classTotal('income', null, $to) - $this->ledger->classTotal('expense', null, $to), 2);
        $totalLiab = round(array_sum(array_column($liabilities, 'amount')), 2);
        $totalEquity = round(array_sum(array_column($equity, 'amount')) + $currentProfit, 2);
        if ($request->filled('export')) {
            $rows = [];
            foreach ($assets as $r) $rows[] = ['Asset', $r['account']->name, $this->n($r['amount'])];
            $rows[] = ['', 'Total Assets', $this->n($totalAssets), '_total' => true];
            foreach ($liabilities as $r) $rows[] = ['Liability', $r['account']->name, $this->n($r['amount'])];
            foreach ($equity as $r) $rows[] = ['Equity', $r['account']->name, $this->n($r['amount'])];
            $rows[] = ['Equity', 'Current period profit', $this->n($currentProfit)];
            $rows[] = ['', 'Total Liabilities & Equity', $this->n($totalLiab + $totalEquity), '_total' => true];
            return $this->export($request, 'Balance Sheet', ['As of' => $to], ['Section', 'Account', 'Amount'], $rows, [2]);
        }
        return view('accounting.reports.balance_sheet', compact('assets', 'liabilities', 'equity', 'totalAssets', 'totalLiab', 'totalEquity', 'currentProfit', 'to'));
    }

    public function receivables(Request $request)
    {
        $rows = $this->partyRows($this->ledger->outstandingByParty('asset'));
        if ($request->filled('export')) return $this->exportParties($request, 'Receivables', $rows);
        return view('accounting.reports.outstanding', ['title' => 'Receivables', 'kind' => 'receivable', 'rows' => $rows]);
    }

    public function payables(Request $request)
    {
        $rows = array_map(function ($r) { $r['balance'] = -$r['balance']; return $r; }, $this->ledger->outstandingByParty('liability'));
        $rows = $this->partyRows($rows);
        if ($request->filled('export')) return $this->exportParties($request, 'Payables', $rows);
        return view('accounting.reports.outstanding', ['title' => 'Payables', 'kind' => 'payable', 'rows' => $rows]);
    }

    public function partyLedger(Request $request)
    {
        [$from, $to] = $this->range($request);
        $type = $request->get('party_type');
        $id = $request->get('party_id');
        $data = ($type && $id) ? $this->ledger->partyLedger($type, (int) $id, $from, $to) : null;
        if ($request->filled('export') && $data) {
            $rows = [];
            foreach ($data['rows'] as $r) $rows[] = [$r['line']->tdate, $r['line']->voucher_no, $r['line']->account_name, $this->n($r['line']->debit), $this->n($r['line']->credit), $this->n($r['running'])];
            $rows[] = ['', '', 'Closing', '', '', $this->n($data['closing']), '_total' => true];
            return $this->export($request, 'Party Ledger', ['Party' => $type.' #'.$id, 'From' => $from, 'To' => $to],
                ['Date', 'Voucher', 'Account', 'Debit', 'Credit', 'Balance'], $rows, [3, 4, 5]);
        }
        return view('accounting.reports.party_ledger', [
            'data' => $data, 'party_type' => $type, 'party_id' => $id, 'from' => $from, 'to' => $to,
            'principals' => Principal::orderBy('name')->get(),
            'partners' => User::where('user_type', 'partner')->orderBy('name')->get(),
        ]);
    }

    public function cashFlow(Request $request)
    {
        [$from, $to] = $this->range($request);
        $accounts = Account::where('is_cash_bank', true)->orderBy('code')->get();
        $data = [];
        $ti = $to2 = 0;
        foreach ($accounts as $a) {
            $opening = round($a->openingSigned() + $this->ledger->movement($a->id, null, date('Y-m-d', strtotime($from.' -1 day'))), 2);
            $in = (float) TransactionLine::join('transactions as t', 't.id', '=', 'transaction_lines.transaction_id')
                ->where('t.status', 'posted')->whereNull('t.deleted_at')->whereBetween('t.date', [$from, $to])
                ->where('account_id', $a->id)->sum('debit');
            $out = (float) TransactionLine::join('transactions as t', 't.id', '=', 'transaction_lines.transaction_id')
                ->where('t.status', 'posted')->whereNull('t.deleted_at')->whereBetween('t.date', [$from, $to])
                ->where('account_id', $a->id)->sum('credit');
            $ti += $in; $to2 += $out;
            $data[] = ['account' => $a, 'opening' => $opening, 'in' => round($in, 2), 'out' => round($out, 2), 'closing' => round($opening + $in - $out, 2)];
        }
        if ($request->filled('export')) {
            $rows = [];
            foreach ($data as $d) $rows[] = [$d['account']->name, $this->n($d['opening']), $this->n($d['in']), $this->n($d['out']), $this->n($d['closing'])];
            $rows[] = ['TOTAL', '', $this->n($ti), $this->n($to2), '', '_total' => true];
            return $this->export($request, 'Cash Flow', ['From' => $from, 'To' => $to], ['Account', 'Opening', 'Inflow', 'Outflow', 'Closing'], $rows, [1, 2, 3, 4]);
        }
        return view('accounting.reports.cash_flow', compact('data', 'from', 'to', 'ti', 'to2'));
    }

    public function taxReport(Request $request)
    {
        [$from, $to] = $this->range($request);
        $account = Account::where('code', '2160')->first();
        $data = $account ? $this->ledger->generalLedger($account, $from, $to) : null;
        if ($request->filled('export') && $data) {
            $rows = [['', '', 'Opening', '', '', $this->n($data['opening'])]];
            foreach ($data['rows'] as $r) $rows[] = [$r['line']->tdate, $r['line']->voucher_no, $r['line']->narration, $this->n($r['line']->debit), $this->n($r['line']->credit), $this->n($r['running'])];
            $rows[] = ['', '', 'Closing', '', '', $this->n($data['closing']), '_total' => true];
            return $this->export($request, 'Tax-VAT Report', ['From' => $from, 'To' => $to], ['Date', 'Voucher', 'Narration', 'Debit', 'Credit', 'Balance'], $rows, [3, 4, 5]);
        }
        return view('accounting.reports.tax', compact('account', 'data', 'from', 'to'));
    }

    public function closeBooks(Request $request)
    {
        abort_unless($request->user()->can('accounting.post'), 403);
        $data = $request->validate(['books_closed_upto' => ['nullable', 'date']]);
        Setting::put('books_closed_upto', $data['books_closed_upto'] ?? '');
        return back()->with('status', 'Books close date updated.');
    }

    // ---------- helpers ----------
    protected function exportParties(Request $request, string $title, array $rows)
    {
        $out = [];
        foreach ($rows as $r) $out[] = [ucfirst($r['party_type']), $r['name'], $this->n($r['balance'])];
        $out[] = ['', 'TOTAL', $this->n(collect($rows)->sum('balance')), '_total' => true];
        return $this->export($request, $title, ['As of' => now()->toDateString()], ['Party Type', 'Party', 'Balance'], $out, [2]);
    }

    protected function export(Request $request, string $title, array $meta, array $columns, array $rows, array $numeric)
    {
        $fname = preg_replace('/[^A-Za-z0-9]+/', '-', $title).'-'.now()->format('Ymd');
        if ($request->get('export') === 'excel') {
            $plain = array_map(fn ($row) => array_map(fn ($i) => $row[$i] ?? '', array_keys($columns)), $rows);
            return Excel::download(new AccountingReportExport($title, $meta, $columns, $plain), $fname.'.xlsx');
        }
        $orientation = count($columns) > 4 ? 'landscape' : 'portrait';
        return Pdf::loadView('pdf.report', compact('title', 'meta', 'columns', 'rows', 'numeric'))
            ->setPaper('a4', $orientation)->download($fname.'.pdf');
    }

    protected function typeBalances(string $type, string $to, bool $creditPositive = false): array
    {
        $rows = [];
        foreach (Account::where('type', $type)->where('is_group', false)->orderBy('code')->get() as $a) {
            $bal = $this->ledger->balance($a, $to);
            $val = $creditPositive ? -$bal : $bal;
            if (abs($val) < 0.005) continue;
            $rows[] = ['account' => $a, 'amount' => round($val, 2)];
        }
        return $rows;
    }

    protected function partyRows(array $rows): array
    {
        return array_map(function ($r) {
            $name = match ($r['party_type']) {
                'principal' => optional(Principal::find($r['party_id']))->name,
                'crew' => optional(CrewProfile::find($r['party_id']))->name,
                'partner', 'staff' => optional(User::find($r['party_id']))->name,
                default => 'Other',
            };
            $r['name'] = $name ?: ('#'.$r['party_id']);
            return $r;
        }, $rows);
    }

    protected function n($v): string { return number_format((float) $v, 2); }

    protected function range(Request $request): array
    {
        return [
            $request->get('from', now()->startOfMonth()->toDateString()),
            $request->get('to', now()->toDateString()),
        ];
    }
}
