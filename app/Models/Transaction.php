<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Transaction extends Model implements Auditable
{
    use SoftDeletes, AuditableTrait;

    protected $guarded = ['id'];
    protected $casts = ['date' => 'date'];

    public function lines() { return $this->hasMany(TransactionLine::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }

    public function totalDebit(): float { return (float) $this->lines->sum('debit'); }
    public function totalCredit(): float { return (float) $this->lines->sum('credit'); }
}
