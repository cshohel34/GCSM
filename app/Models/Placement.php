<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Placement extends Model implements Auditable
{
    use AuditableTrait;

    protected $guarded = ['id'];
    protected $casts = [
        'sign_on_date' => 'date',
        'expected_joining_date' => 'date',
        'sign_off_date' => 'date',
        'service_charge' => 'decimal:2',
        'has_dues' => 'boolean',
    ];

    public function crewProfile() { return $this->belongsTo(CrewProfile::class); }
    public function principal() { return $this->belongsTo(Principal::class); }
    public function vessel() { return $this->belongsTo(PrincipalVessel::class, 'principal_vessel_id'); }
    public function arrangedBy() { return $this->belongsTo(User::class, 'arranged_by'); }
}
