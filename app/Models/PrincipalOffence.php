<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class PrincipalOffence extends Model implements Auditable {
    use AuditableTrait;
    protected $guarded = ['id'];
    protected $casts = ['offence_date' => 'date'];
    public function principal() { return $this->belongsTo(Principal::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }
}
