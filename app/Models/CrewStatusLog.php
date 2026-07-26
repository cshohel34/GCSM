<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrewStatusLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'old_deadline'       => 'date',
        'new_deadline'       => 'date',
        'old_available_from' => 'date',
        'new_available_from' => 'date',
    ];

    public function crewProfile() { return $this->belongsTo(CrewProfile::class); }
    public function changedBy()   { return $this->belongsTo(User::class, 'changed_by'); }

    public function getContextLabelAttribute(): string
    {
        return match ($this->context) {
            'sign_off'          => 'Sign-off',
            'placement_history' => 'Placement History',
            'personal_details'  => 'Personal Details',
            default             => ucfirst(str_replace('_', ' ', (string) $this->context)),
        };
    }
}
