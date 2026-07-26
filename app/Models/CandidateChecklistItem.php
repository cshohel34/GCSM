<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class CandidateChecklistItem extends Model implements Auditable {
    use AuditableTrait;
    protected $guarded = ['id'];
    protected $casts = [
        'is_received' => 'boolean',
        'required' => 'boolean',
        'remark_at' => 'datetime',
    ];
    public function candidate() { return $this->belongsTo(Candidate::class); }
    public function remarkBy() { return $this->belongsTo(User::class, 'remark_by'); }

    /** Auto-mapped items are driven by the crew profile and cannot be toggled by hand. */
    public function isAutoMapped(): bool
    {
        return $this->code && \App\Services\CandidateChecklist::isAutoCode($this->code);
    }
}
