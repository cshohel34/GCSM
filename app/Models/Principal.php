<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Principal extends Model implements Auditable
{
    use SoftDeletes, AuditableTrait;

    protected $guarded = ['id'];

    public function contacts() { return $this->hasMany(PrincipalContact::class); }
    public function vessels() { return $this->hasMany(PrincipalVessel::class); }
    public function documents() { return $this->hasMany(PrincipalDocument::class); }
    public function companyNotes() { return $this->hasMany(PrincipalNote::class)->latest(); }
    public function offences() { return $this->hasMany(PrincipalOffence::class)->latest(); }
    public function assignments() { return $this->hasMany(PrincipalStaffAssignment::class)->latest('assigned_at'); }
    public function assignedStaff() { return $this->belongsTo(User::class, 'assigned_staff_id'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function placements() { return $this->hasMany(Placement::class); }
    public function requisitions() { return $this->hasMany(Requisition::class); }
    public function salarySheets() { return $this->hasMany(SalarySheet::class); }

    public function currentCrew()
    {
        return $this->placements()->where('status', 'onboard');
    }

    public function hasContract(): bool
    {
        return $this->documents()->where('doc_type', 'contract')->exists();
    }

    public function scopeSearch(Builder $q, array $f): Builder
    {
        return $q
            ->when($f['name'] ?? null, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when($f['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($f['country'] ?? null, fn ($q, $v) => $q->where('country', 'like', "%{$v}%"))
            ->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($f['staff_id'] ?? null, fn ($q, $v) => $q->where('assigned_staff_id', $v));
    }
}
