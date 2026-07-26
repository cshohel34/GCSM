<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionLine extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['debit' => 'decimal:2', 'credit' => 'decimal:2'];

    public function transaction() { return $this->belongsTo(Transaction::class); }
    public function account() { return $this->belongsTo(Account::class); }

    /** Resolve the human name of the attributed party (subsidiary ledger). */
    public function partyName(): ?string
    {
        if (! $this->party_type || ! $this->party_id) return null;
        return match ($this->party_type) {
            'principal' => optional(Principal::find($this->party_id))->name,
            'crew' => optional(CrewProfile::find($this->party_id))->name,
            'partner', 'staff' => optional(User::find($this->party_id))->name,
            default => $this->memo,
        };
    }

    public static function partyOptions(): array
    {
        return ['principal' => 'Principal / Company', 'crew' => 'Crew', 'partner' => 'Partner', 'staff' => 'Staff', 'other' => 'Other'];
    }
}
