<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class PrincipalNote extends Model implements Auditable {
    use AuditableTrait;
    protected $guarded = ['id'];
    public function principal() { return $this->belongsTo(Principal::class); }
    public function author() { return $this->belongsTo(User::class, 'author_id'); }
}
