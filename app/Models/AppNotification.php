<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AppNotification extends Model {
    protected $guarded = ['id'];
    protected $casts = ['read_at' => 'datetime'];
    public function user() { return $this->belongsTo(User::class); }
    public function crewProfile() { return $this->belongsTo(CrewProfile::class); }
    public function scopeUnread($q) { return $q->whereNull('read_at'); }
}
