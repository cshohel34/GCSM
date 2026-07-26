<?php

namespace App\Http\Controllers;

use App\Models\PartnerPayout;
use App\Models\Placement;
use App\Models\User;
use App\Models\Account;
use App\Services\PostingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerPayoutController extends Controller
{
    public function store(Request $request, User $staff)
    {
        abort_unless($staff->isPartner(), 422, 'Payouts apply to partners only.');
        $data = $request->validate([
            'placement_id' => ['nullable', 'exists:placements,id'],
            'basis' => ['required', Rule::in(['service_charge', 'agency_fee', 'net_profit', 'negotiated'])],
            'base_amount' => ['nullable', 'numeric', 'min:0'],
            'percent' => ['nullable', 'numeric', 'between:0,100'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        // If amount not given, compute base × percent.
        $amount = $data['amount'] ?? null;
        if ($amount === null && isset($data['base_amount'], $data['percent'])) {
            $amount = round($data['base_amount'] * $data['percent'] / 100, 2);
        }

        $payout = PartnerPayout::create([
            'partner_id' => $staff->id,
            'placement_id' => $data['placement_id'] ?? null,
            'basis' => $data['basis'],
            'base_amount' => $data['base_amount'] ?? null,
            'percent' => $data['percent'] ?? null,
            'amount' => $amount ?? 0,
            'notes' => $data['notes'] ?? null,
            'recorded_by' => $request->user()->id,
        ]);
        // Accrue: Dr Partner Commission (5010) / Cr Partner Payable (2120).
        $this->post('journal', 'Partner commission accrued — '.$staff->name, [
            ['code' => '5010', 'debit' => $payout->amount],
            ['code' => '2120', 'credit' => $payout->amount, 'party' => $staff->id],
        ], $request->user()->id, $payout->id);
        return back()->with('status', 'Payout recorded and accrued in accounts.');
    }

    public function markPaid(User $staff, PartnerPayout $payout)
    {
        abort_unless($payout->partner_id === $staff->id, 404);
        $payout->update(['status' => 'paid', 'paid_date' => now()]);
        $this->post('payment', 'Partner commission paid — '.$staff->name, [
            ['code' => '2120', 'debit' => $payout->amount, 'party' => $staff->id],
            ['code' => '1120', 'credit' => $payout->amount],
        ], auth()->id(), $payout->id);
        return back()->with('status', 'Marked paid and posted.');
    }

    public function destroy(User $staff, PartnerPayout $payout)
    {
        abort_unless($payout->partner_id === $staff->id, 404);
        $payout->delete();
        return back()->with('status', 'Payout removed.');
    }

    protected function post(string $type, string $narration, array $lines, ?int $userId, int $sourceId): void
    {
        $rows = [];
        foreach ($lines as $l) {
            $acc = Account::where('code', $l['code'])->first();
            if (! $acc) return;
            $rows[] = [
                'account_id' => $acc->id,
                'debit' => $l['debit'] ?? 0, 'credit' => $l['credit'] ?? 0,
                'party_type' => isset($l['party']) ? 'partner' : null, 'party_id' => $l['party'] ?? null,
            ];
        }
        try {
            app(PostingService::class)->record([
                'voucher_type' => $type, 'date' => now()->toDateString(),
                'narration' => $narration, 'created_by' => $userId,
                'source_type' => 'PartnerPayout', 'source_id' => $sourceId,
            ], $rows);
        } catch (\Throwable $e) {
            \Log::warning('[Partner payout post] '.$e->getMessage());
        }
    }
}
