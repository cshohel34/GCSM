<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class SignOnLetter extends Model implements Auditable
{
    use AuditableTrait;

    protected $guarded = ['id'];
    protected $casts = [
        'letter_date' => 'date',
        'joining_date' => 'date',
        'passport_issue' => 'date',
    ];

    public function crewProfile() { return $this->belongsTo(CrewProfile::class); }
    public function principal() { return $this->belongsTo(Principal::class); }
    public function vessel() { return $this->belongsTo(PrincipalVessel::class, 'principal_vessel_id'); }
    public function issuedBy() { return $this->belongsTo(User::class, 'issued_by'); }

    public function scopeSearch(Builder $q, array $f): Builder
    {
        return $q
            ->when($f['crew'] ?? null, fn ($q, $v) => $q->where('crew_name', 'like', "%{$v}%"))
            ->when($f['cdc_no'] ?? null, fn ($q, $v) => $q->where('cdc_no', 'like', "%{$v}%"))
            ->when($f['passport_no'] ?? null, fn ($q, $v) => $q->where('passport_no', 'like', "%{$v}%"))
            ->when($f['mobile'] ?? null, fn ($q, $v) => $q->where('mobile', 'like', "%{$v}%"))
            ->when($f['vessel'] ?? null, fn ($q, $v) => $q->where('vessel_name', 'like', "%{$v}%"))
            ->when($f['company'] ?? null, fn ($q, $v) => $q->where('company_name', 'like', "%{$v}%"))
            ->when($f['rank'] ?? null, fn ($q, $v) => $q->where('rank', 'like', "%{$v}%"))
            ->when($f['reference'] ?? null, fn ($q, $v) => $q->where('reference_no', 'like', "%{$v}%"))
            ->when($f['joining_date'] ?? null, fn ($q, $v) => $q->whereDate('joining_date', $v))
            ->when($f['date_from'] ?? null, fn ($q, $v) => $q->whereDate('letter_date', '>=', $v))
            ->when($f['date_to'] ?? null, fn ($q, $v) => $q->whereDate('letter_date', '<=', $v));
    }
}
