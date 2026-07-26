<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class CvSubmission extends Model implements Auditable
{
    use AuditableTrait;

    protected $guarded = ['id'];
    protected $casts = ['date_of_birth' => 'date', 'reviewed_at' => 'datetime'];

    public function reviewedBy() { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function crewProfile() { return $this->belongsTo(CrewProfile::class); }
}
