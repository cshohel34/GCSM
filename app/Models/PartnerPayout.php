<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PartnerPayout extends Model {
    protected $guarded = ['id'];
    protected $casts = ['base_amount' => 'decimal:2', 'percent' => 'decimal:2', 'amount' => 'decimal:2', 'paid_date' => 'date'];
    public function partner() { return $this->belongsTo(User::class, 'partner_id'); }
    public function placement() { return $this->belongsTo(Placement::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }
}
