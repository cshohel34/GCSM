<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RequisitionPosition extends Model {
    protected $guarded = ['id'];
    public function requisition() { return $this->belongsTo(Requisition::class); }
    public function rank() { return $this->belongsTo(Rank::class); }
    public function vessel() { return $this->belongsTo(PrincipalVessel::class, 'principal_vessel_id'); }
    public function candidates() { return $this->hasMany(Candidate::class); }

    // Funnel counts (CS-06)
    public function countAt(array $stages): int
    {
        return $this->candidates->whereIn('stage', $stages)->count();
    }
}
