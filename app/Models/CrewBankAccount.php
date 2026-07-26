<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
class CrewBankAccount extends Model implements Auditable {
    use AuditableTrait;
    protected $guarded = ['id'];
    protected $casts = ['is_own_account' => 'boolean', 'is_primary' => 'boolean'];
    public function crewProfile() { return $this->belongsTo(CrewProfile::class); }
}
