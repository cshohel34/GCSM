<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PrincipalStaffAssignment extends Model {
    protected $guarded = ['id'];
    protected $casts = ['assigned_at' => 'datetime', 'unassigned_at' => 'datetime'];
    public function principal() { return $this->belongsTo(Principal::class); }
    public function staff() { return $this->belongsTo(User::class, 'staff_id'); }
    public function assignedBy() { return $this->belongsTo(User::class, 'assigned_by'); }
}
