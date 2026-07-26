<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StaffSalary extends Model {
    protected $guarded = ['id'];
    protected $casts = ['paid_date' => 'date'];
    public function user() { return $this->belongsTo(User::class); }
    public function recompute(): void {
        $this->net = round((float)$this->basic + (float)$this->allowance + (float)$this->bonus - (float)$this->deduction, 2);
    }
}
