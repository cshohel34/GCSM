<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Candidate extends Model implements Auditable
{
    use AuditableTrait;

    protected $guarded = ['id'];
    protected $casts = [
        'forwarded_at' => 'datetime',
        'interview_date' => 'date',
        'confirmed_at' => 'date',
        'service_charge' => 'decimal:2',
        'service_charge_received' => 'boolean',
        'service_charge_decided' => 'boolean',
        'documents_complete' => 'boolean',
    ];

    public const STAGES = [
        'wishlisted' => 'Wishlisted',
        'shortlisted' => 'Shortlisted',
        'forwarded' => 'Forwarded',
        'rejected_by_company' => 'Rejected by company',
        'interview_selected' => 'Interview selected',
        'interview_passed' => 'Interview passed',
        'interview_failed' => 'Interview failed',
        'final_selected' => 'Final selected',
        'signed_on' => 'Signed on',
    ];

    public function position() { return $this->belongsTo(RequisitionPosition::class, 'requisition_position_id'); }
    public function crewProfile() { return $this->belongsTo(CrewProfile::class); }
    public function placement() { return $this->belongsTo(Placement::class); }
    public function arrangedBy() { return $this->belongsTo(User::class, 'arranged_by'); }
    public function checklistItems() { return $this->hasMany(CandidateChecklistItem::class); }

    public function stageLabel(): string
    {
        return self::STAGES[$this->stage] ?? $this->stage;
    }
}
