<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class CrewSeaService extends Model implements Auditable {
    use AuditableTrait;
    protected $guarded = ['id'];
    protected $casts = ['sign_on' => 'date', 'sign_off' => 'date'];
    public function crewProfile() { return $this->belongsTo(CrewProfile::class); }
}
