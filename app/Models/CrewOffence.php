<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class CrewOffence extends Model implements Auditable {
    use AuditableTrait;
    protected $guarded = ['id'];
    protected $casts = ['offence_date' => 'date'];
    public function crewProfile() { return $this->belongsTo(CrewProfile::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }
}
