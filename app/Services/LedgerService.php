<?php

namespace App\Services;

use App\Models\Account;
use App\Models\TransactionLine;
use Illuminate\Support\Facades\DB;

/** Report/query engine over the double-entry ledger. Balances are debit-positive. */
class LedgerService
{
    /** Sum of (debit - credit) for an account between dates (posted only). */
    public function movement(int $accountId, ?string $from = null, ?string $to = null): float
    {
        return (float) $this->linesQuery($from, $to)
            ->where('transaction_lines.account_id', $accountId)
            ->selectRaw('COALESCE(SUM(debit - credit),0) as agg')
            ->value('agg');
    }

    /** Signed balance (debit-positive) up to a date, including opening. */
    public function balance(Account $account, ?string $upto = null): float
    {
        return round($account->openingSigned() + $this->movement($account->id, null, $upto), 2);
    }

    /** Trial balance rows for postable accounts. */
    public function trialBalance(?string $from = null, ?string $to = null): array
    {
        $rows = [];
        foreach (Account::where('is_group', false)->orderBy('code')->get() as $a) {
            $bal = round($a->openingSigned() + $this->movement($a->id, null, $to), 2);
            if (abs($bal) < 0.005 && $this->movement($a->id, null, $to) == 0.0) continue;
            $rows[] = [
                'account' => $a,
                'debit' => $bal > 0 ? $bal : 0,
                'credit' => $bal < 0 ? -$bal : 0,
            ];
        }
        return $rows;
    }

    /** Class totals for income/expense over a period (for P&L). */
    public function classTotal(string $type, ?string $from, ?string $to): float
    {
        // income/equity/liability are credit-normal -> return positive as credit balance
        $sum = (float) $this->linesQuery($from, $to)
            ->join('accounts', 'accounts.id', '=', 'transaction_lines.account_id')
            ->where('accounts.type', $type)
            ->selectRaw('COALESCE(SUM(debit - credit),0) as agg')
            ->value('agg');
        return in_array($type, ['income', 'liability', 'equity']) ? -$sum : $sum;
    }

    /** Per-account totals for a class over a period. */
    public function accountsByType(string $type, ?string $from, ?string $to, bool $creditPositive = false): array
    {
        $rows = [];
        foreach (Account::where('type', $type)->where('is_group', false)->orderBy('code')->get() as $a) {
            $m = $this->movement($a->id, $from, $to);
            $val = $creditPositive ? -$m : $m;
            if (abs($val) < 0.005) continue;
            $rows[] = ['account' => $a, 'amount' => round($val, 2)];
        }
        return $rows;
    }

    /** General ledger for one account with running balance. */
    public function generalLedger(Account $account, ?string $from, ?string $to): array
    {
        $opening = round($account->openingSigned() + $this->movement($account->id, null, $from ? date('Y-m-d', strtotime($from.' -1 day')) : null), 2);
        $lines = $this->linesQuery($from, $to)
            ->where('transaction_lines.account_id', $account->id)
            ->join('transactions', 'transactions.id', '=', 'transaction_lines.transaction_id')
            ->orderBy('transactions.date')->orderBy('transactions.id')
            ->select('transaction_lines.*', 'transactions.date as tdate', 'transactions.voucher_no', 'transactions.voucher_type', 'transactions.narration')
            ->get();
        $running = $opening;
        $out = [];
        foreach ($lines as $l) {
            $running += ((float) $l->debit - (float) $l->credit);
            $out[] = ['line' => $l, 'running' => round($running, 2)];
        }
        return ['opening' => $opening, 'rows' => $out, 'closing' => round($running, 2)];
    }

    /** Party (subsidiary) ledger — receivable if debit-positive, payable if negative. */
    public function partyLedger(string $partyType, int $partyId, ?string $from, ?string $to): array
    {
        $rows = $this->linesQuery($from, $to)
            ->where('party_type', $partyType)->where('party_id', $partyId)
            ->join('transactions', 'transactions.id', '=', 'transaction_lines.transaction_id')
            ->join('accounts', 'accounts.id', '=', 'transaction_lines.account_id')
            ->orderBy('transactions.date')->orderBy('transactions.id')
            ->select('transaction_lines.*', 'transactions.date as tdate', 'transactions.voucher_no', 'accounts.name as account_name')
            ->get();
        $running = 0.0; $out = [];
        foreach ($rows as $r) {
            $running += ((float) $r->debit - (float) $r->credit);
            $out[] = ['line' => $r, 'running' => round($running, 2)];
        }
        return ['rows' => $out, 'closing' => round($running, 2)];
    }

    /** Outstanding party balances against a set of account types (receivables/payables). */
    public function outstandingByParty(string $accountType): array
    {
        $q = TransactionLine::query()
            ->join('transactions', 'transactions.id', '=', 'transaction_lines.transaction_id')
            ->join('accounts', 'accounts.id', '=', 'transaction_lines.account_id')
            ->where('transactions.status', 'posted')
            ->whereNull('transactions.deleted_at')
            ->where('accounts.type', $accountType)
            ->whereNotNull('party_type')
            ->groupBy('party_type', 'party_id')
            ->havingRaw('ABS(SUM(debit - credit)) > 0.005')
            ->select('party_type', 'party_id', DB::raw('SUM(debit - credit) as bal'))
            ->get();
        return $q->map(fn ($r) => [
            'party_type' => $r->party_type,
            'party_id' => $r->party_id,
            'balance' => round((float) $r->bal, 2),
        ])->all();
    }

    protected function linesQuery(?string $from, ?string $to)
    {
        return TransactionLine::query()
            ->join('transactions as t', 't.id', '=', 'transaction_lines.transaction_id')
            ->where('t.status', 'posted')
            ->whereNull('t.deleted_at')
            ->when($from, fn ($q) => $q->whereDate('t.date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('t.date', '<=', $to));
    }
}
