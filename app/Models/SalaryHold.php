<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SalaryHold extends Model {
    protected $guarded = ['id'];
    protected $casts = ['released_at' => 'datetime'];
    public function crewProfile() { return $this->belongsTo(CrewProfile::class); }
    public function line() { return $this->belongsTo(SalaryLine::class, 'salary_line_id'); }
    public function heldBy() { return $this->belongsTo(User::class, 'held_by'); }
}
