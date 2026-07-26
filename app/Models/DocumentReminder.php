<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DocumentReminder extends Model {
    protected $guarded = ['id'];
    protected $casts = ['channels' => 'array', 'expiry_date' => 'date', 'sent_for_date' => 'date', 'sent_at' => 'datetime'];
    public function crewProfile() { return $this->belongsTo(CrewProfile::class); }
}
