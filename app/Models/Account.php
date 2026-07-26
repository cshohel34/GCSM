<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Account extends Model implements Auditable
{
    use AuditableTrait;

    protected $guarded = ['id'];
    protected $casts = [
        'is_group' => 'boolean',
        'is_cash_bank' => 'boolean',
        'active' => 'boolean',
        'opening_balance' => 'decimal:2',
    ];

    public function parent() { return $this->belongsTo(Account::class, 'parent_id'); }
    public function children() { return $this->hasMany(Account::class, 'parent_id'); }
    public function lines() { return $this->hasMany(TransactionLine::class); }

    public function isDebitNormal(): bool
    {
        return in_array($this->type, ['asset', 'expense']);
    }

    /** Opening balance as a signed debit-positive number. */
    public function openingSigned(): float
    {
        $amt = (float) $this->opening_balance;
        if ($amt == 0.0) return 0.0;
        $side = $this->opening_side ?: ($this->isDebitNormal() ? 'debit' : 'credit');
        return $side === 'debit' ? $amt : -$amt;
    }
}
