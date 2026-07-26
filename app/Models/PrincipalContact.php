<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class PrincipalContact extends Model implements Auditable {
    use AuditableTrait;
    protected $guarded = ['id'];
    protected $casts = ['is_primary' => 'boolean'];
    public function principal() { return $this->belongsTo(Principal::class); }
}
