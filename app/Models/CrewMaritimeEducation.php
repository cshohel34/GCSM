<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class CrewMaritimeEducation extends Model implements Auditable {
    use AuditableTrait;
    protected $table = 'crew_maritime_educations';
    protected $guarded = ['id'];
    public function crewProfile() { return $this->belongsTo(CrewProfile::class); }
}
