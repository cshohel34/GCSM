<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class CrewNote extends Model implements Auditable {
    use AuditableTrait;
    protected $guarded = ['id'];
    public function crewProfile() { return $this->belongsTo(CrewProfile::class); }
    public function author() { return $this->belongsTo(User::class, 'author_id'); }
}
