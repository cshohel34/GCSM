<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Creates balanced double-entry transactions. Every voucher must have
 * total debit == total credit (CA rule). Other modules can call record()
 * to auto-post (e.g. salary lock, partner payout).
 */
class PostingService
{
    /**
     * @param array $header ['voucher_type','date','narration','reference','created_by','source_type','source_id']
     * @param array $lines  each ['account_id','debit','credit','party_type','party_id','memo']
     */
    public function record(array $header, array $lines): Transaction
    {
        $lines = array_values(array_filter($lines, fn ($l) =>
            (float) ($l['debit'] ?? 0) != 0.0 || (float) ($l['credit'] ?? 0) != 0.0));

        if (count($lines) < 2) {
            throw ValidationException::withMessages(['lines' => 'A voucher needs at least two lines.']);
        }
        $dr = round(array_sum(array_map(fn ($l) => (float) ($l['debit'] ?? 0), $lines)), 2);
        $cr = round(array_sum(array_map(fn ($l) => (float) ($l['credit'] ?? 0), $lines)), 2);
        if ($dr !== $cr) {
            throw ValidationException::withMessages(['lines' => "Debit ({$dr}) must equal Credit ({$cr})."]);
        }
        if ($dr <= 0) {
            throw ValidationException::withMessages(['lines' => 'Voucher amount must be greater than zero.']);
        }
        $status = $header['status'] ?? 'posted';
        // Drafts are provisional and don't hit the ledger, so the closed-books rule
        // only applies to posted vouchers.
        if ($status !== 'draft') {
            $closed = Setting::get('books_closed_upto');
            if ($closed && $header['date'] <= $closed) {
                throw ValidationException::withMessages(['date' => "Books are closed up to {$closed}. Use a later date."]);
            }
        }

        return DB::transaction(function () use ($header, $lines, $status) {
            $type = $header['voucher_type'] ?? 'journal';
            $txn = Transaction::create([
                'voucher_no' => $this->nextVoucherNo($type),
                'voucher_type' => $type,
                'date' => $header['date'],
                'reference' => $header['reference'] ?? null,
                'narration' => $header['narration'] ?? null,
                'status' => $status,
                'source_type' => $header['source_type'] ?? null,
                'source_id' => $header['source_id'] ?? null,
                'created_by' => $header['created_by'] ?? null,
            ]);
            foreach ($lines as $l) {
                $txn->lines()->create([
                    'account_id' => $l['account_id'],
                    'debit' => round((float) ($l['debit'] ?? 0), 2),
                    'credit' => round((float) ($l['credit'] ?? 0), 2),
                    'party_type' => $l['party_type'] ?? null,
                    'party_id' => $l['party_id'] ?? null,
                    'memo' => $l['memo'] ?? null,
                ]);
            }
            return $txn;
        });
    }

    public function nextVoucherNo(string $type): string
    {
        $prefix = ['receipt' => 'RV', 'payment' => 'PV', 'journal' => 'JV', 'contra' => 'CV'][$type] ?? 'JV';
        $year = now()->format('Y');
        $count = Transaction::where('voucher_type', $type)
            ->whereYear('created_at', $year)->withTrashed()->count() + 1;
        return sprintf('%s-%s-%04d', $prefix, $year, $count);
    }
}
