<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class CrewCourse extends Model implements Auditable {
    use AuditableTrait;
    protected $guarded = ['id'];
    protected $casts = ['completion_date' => 'date', 'issue_date' => 'date', 'expiry_date' => 'date'];
    public function crewProfile() { return $this->belongsTo(CrewProfile::class); }
    public function catalogue() { return $this->belongsTo(CourseCatalogue::class, 'course_catalogue_id'); }
}
