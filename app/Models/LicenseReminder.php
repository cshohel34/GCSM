<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LicenseReminder extends Model {
    protected $guarded = ['id'];
    protected $casts = ['channels' => 'array', 'expiry_date' => 'date', 'sent_for_date' => 'date', 'sent_at' => 'datetime'];
    public function license() { return $this->belongsTo(CompanyLicense::class, 'company_license_id'); }
}
