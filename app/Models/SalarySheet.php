<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class SalarySheet extends Model implements Auditable
{
    use SoftDeletes, AuditableTrait;

    protected $guarded = ['id'];
    protected $casts = [
        'usd_rate' => 'decimal:4',
        'approved_at' => 'datetime',
    ];

    public function principal() { return $this->belongsTo(Principal::class); }
    public function vessel() { return $this->belongsTo(PrincipalVessel::class, 'principal_vessel_id'); }
    public function lines() { return $this->hasMany(SalaryLine::class)->orderBy('sl_no'); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by'); }

    public function isLocked(): bool { return $this->status === 'locked'; }
    public function isEditable(): bool { return in_array($this->status, ['draft', 'reconciled']); }

    // Totals for the footer / reports
    public function totalNetBdt(): float { return (float) $this->lines->sum('net_bdt'); }
    public function totalAgentBdt(): float { return (float) $this->lines->sum('agent_net_bdt'); }

    public function scopeSearch(Builder $q, array $f): Builder
    {
        return $q
            ->when($f['principal_id'] ?? null, fn ($q, $v) => $q->where('principal_id', $v))
            ->when($f['month'] ?? null, fn ($q, $v) => $q->where('month', 'like', "%{$v}%"))
            ->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v));
    }
}
