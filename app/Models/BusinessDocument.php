<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class BusinessDocument extends Model implements Auditable
{
    use AuditableTrait;
    protected $guarded = ['id'];
    protected $casts = ['issue_date' => 'date', 'expiry_date' => 'date'];

    public function computeStatus(): string
    {
        if (! $this->expiry_date) return 'na';
        $today = now()->startOfDay();
        if ($this->expiry_date->lt($today)) return 'expired';
        if ($this->expiry_date->lte($today->copy()->addDays(30))) return 'expiring';
        return 'valid';
    }
}
