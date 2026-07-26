<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class PrincipalVessel extends Model implements Auditable {
    use AuditableTrait;
    protected $guarded = ['id'];
    protected $casts = ['active' => 'boolean'];
    public function principal() { return $this->belongsTo(Principal::class); }
    public function placements() { return $this->hasMany(Placement::class); }
}
