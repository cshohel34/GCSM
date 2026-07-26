<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class CrewProfile extends Model implements Auditable
{
    use HasFactory, SoftDeletes, AuditableTrait;

    protected $guarded = ['id'];

    protected $casts = [
        'date_of_birth' => 'date',
        'blacklist_date' => 'date',
        'oma_synced_at' => 'datetime',
        'job_deadline' => 'date',
        'available_from' => 'date',
    ];

    // ---- Job lifecycle accessors ----

    /** Availability shown to users: a resting crew becomes 'available' once available_from arrives. */
    public function getEffectiveAvailabilityAttribute(): string
    {
        if ($this->availability === 'resting' && $this->available_from && $this->available_from->startOfDay()->lte(now()->startOfDay())) {
            return 'available';
        }
        return $this->availability;
    }

    /** Days left to the placement deadline (negative = overdue). Null if no deadline. */
    public function getDeadlineDaysLeftAttribute(): ?int
    {
        if (! $this->job_deadline) return null;
        return (int) now()->startOfDay()->diffInDays($this->job_deadline->startOfDay(), false);
    }

    /** Days until a resting crew becomes available (negative = due). Null if not resting. */
    public function getRestingDaysLeftAttribute(): ?int
    {
        if ($this->availability !== 'resting' || ! $this->available_from) return null;
        return (int) now()->startOfDay()->diffInDays($this->available_from->startOfDay(), false);
    }

    // ---- Relationships ----
    public function rankApplied() { return $this->belongsTo(Rank::class, 'rank_applied_id'); }
    public function currentRank() { return $this->belongsTo(Rank::class, 'current_rank_id'); }
    public function seaServices() { return $this->hasMany(CrewSeaService::class); }
    public function courses() { return $this->hasMany(CrewCourse::class); }
    public function maritimeEducations() { return $this->hasMany(CrewMaritimeEducation::class); }
    public function academics() { return $this->hasMany(CrewAcademic::class); }
    public function documents() { return $this->hasMany(CrewDocument::class); }
    public function bankAccounts() { return $this->hasMany(CrewBankAccount::class); }
    public function offences() { return $this->hasMany(CrewOffence::class); }
    public function notes() { return $this->hasMany(CrewNote::class); }
    public function statusLogs() { return $this->hasMany(CrewStatusLog::class)->latest(); }
    public function reminders() { return $this->hasMany(DocumentReminder::class); }
    public function placements() { return $this->hasMany(Placement::class); }
    public function salaryLines() { return $this->hasMany(SalaryLine::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }

    // ---- Accessors ----
    public function getHasOffencesAttribute(): bool
    {
        return $this->offences()->exists();
    }

    public function getReminderCountAttribute(): int
    {
        return $this->reminders()->count();
    }

    public function submission() { return $this->hasOne(CvSubmission::class); }

    public function getDisplayIdAttribute(): string
    {
        return $this->admission_id ?: ($this->gc_id ?: ('#'.$this->id));
    }

    public static function generateGcId(): string
    {
        $seq = (int) \App\Models\Setting::get('gc_seq', '0') + 1;
        \App\Models\Setting::put('gc_seq', (string) $seq);
        return 'GC-'.now()->format('Y').'-'.str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }

    // ---- Search scope (CM-05/06) ----
    public function scopeSearch(Builder $q, array $f): Builder
    {
        return $q
            ->when($f['name'] ?? null, fn ($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when($f['cdc_no'] ?? null, fn ($q, $v) => $q->where('cdc_no', 'like', "%{$v}%"))
            ->when($f['passport_no'] ?? null, fn ($q, $v) => $q->where('passport_no', 'like', "%{$v}%"))
            ->when($f['coc_no'] ?? null, fn ($q, $v) => $q->where('coc_no', 'like', "%{$v}%"))
            ->when($f['mobile'] ?? null, fn ($q, $v) => $q->where('mobile', 'like', "%{$v}%"))
            ->when($f['email'] ?? null, fn ($q, $v) => $q->where('email', 'like', "%{$v}%"))
            ->when($f['admission_id'] ?? null, fn ($q, $v) => $q->where('admission_id', 'like', "%{$v}%"))
            ->when($f['rank_id'] ?? null, fn ($q, $v) => $q->where('current_rank_id', $v))
            ->when($f['availability'] ?? null, fn ($q, $v) => $q->where('availability', $v))
            ->when($f['vessel_type'] ?? null, fn ($q, $v) => $q->whereHas('seaServices',
                fn ($q) => $q->where('vessel_type', 'like', "%{$v}%")))
            ->when($f['company_name'] ?? null, fn ($q, $v) => $q->whereHas('seaServices',
                fn ($q) => $q->where('company_name', 'like', "%{$v}%")))
            ->when($f['vessel_name'] ?? null, fn ($q, $v) => $q->whereHas('seaServices',
                fn ($q) => $q->where('vessel_name', 'like', "%{$v}%")))
            ->when($f['owner'] ?? null, fn ($q, $v) => $q->whereHas('seaServices',
                fn ($q) => $q->where('owner', 'like', "%{$v}%")))
            ->when(($f['duration_from'] ?? null) && ($f['duration_to'] ?? null), fn ($q) =>
                $q->whereHas('seaServices', fn ($q) => $q
                    ->whereDate('sign_on', '>=', $f['duration_from'])
                    ->whereDate('sign_off', '<=', $f['duration_to'])));
    }
}