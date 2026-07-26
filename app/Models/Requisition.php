<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Requisition extends Model implements Auditable
{
    use SoftDeletes, AuditableTrait;

    protected $guarded = ['id'];
    protected $casts = ['requirement_date' => 'date', 'deadline' => 'date'];

    /**
     * The deadline has passed once its day is fully over. A requisition with no
     * deadline is never "past" (open-ended). New positions/crew are blocked after
     * this returns true; work on existing candidates is unaffected.
     */
    public function deadlinePassed(): bool
    {
        return $this->deadline && $this->deadline->endOfDay()->isPast();
    }

    public function getDeadlinePassedAttribute(): bool
    {
        return $this->deadlinePassed();
    }

    public function principal() { return $this->belongsTo(Principal::class); }
    public function contact() { return $this->belongsTo(PrincipalContact::class, 'principal_contact_id'); }
    public function positions() { return $this->hasMany(RequisitionPosition::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }

    /** Office staff / partners managing this requirement (two or more allowed). */
    public function assignedStaff()
    {
        return $this->belongsToMany(User::class, 'requisition_staff')
            ->withPivot(['assigned_by', 'note'])
            ->withTimestamps();
    }

    public function scopeSearch(Builder $q, array $f): Builder
    {
        return $q
            ->when($f['principal_id'] ?? null, fn ($q, $v) => $q->where('principal_id', $v))
            ->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($f['reference'] ?? null, fn ($q, $v) => $q->where('reference', 'like', "%{$v}%"))
            ->when($f['date_from'] ?? null, fn ($q, $v) => $q->whereDate('requirement_date', '>=', $v))
            ->when($f['date_to'] ?? null, fn ($q, $v) => $q->whereDate('requirement_date', '<=', $v))
            // Vessel (any position on this requirement)
            ->when($f['vessel_id'] ?? null, fn ($q, $v) => $q->whereHas('positions',
                fn ($w) => $w->where('principal_vessel_id', $v)))
            // Rank (any position on this requirement)
            ->when($f['rank_id'] ?? null, fn ($q, $v) => $q->whereHas('positions',
                fn ($w) => $w->where('rank_id', $v)))
            // Company country
            ->when($f['country'] ?? null, fn ($q, $v) => $q->whereHas('principal',
                fn ($w) => $w->where('country', 'like', "%{$v}%")))
            // Company contact
            ->when($f['contact_id'] ?? null, fn ($q, $v) => $q->where('principal_contact_id', $v))
            // Managed by a staff / partner (assigned to it, or the one who created it)
            ->when($f['staff_id'] ?? null, fn ($q, $v) => $q->where(fn ($w) => $w
                ->whereHas('assignedStaff', fn ($s) => $s->where('users.id', $v))
                ->orWhere('created_by', $v)));
    }
}
