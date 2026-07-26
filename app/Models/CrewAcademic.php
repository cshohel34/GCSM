<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class CrewAcademic extends Model implements Auditable {
    use AuditableTrait;
    protected $table = 'crew_academics';
    protected $guarded = ['id'];
    public function crewProfile() { return $this->belongsTo(CrewProfile::class); }
}
