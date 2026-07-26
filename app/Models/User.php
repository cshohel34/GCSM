<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'user_type', 'status', 'office',
        'date_of_joining', 'date_of_resignation',
        'share_service_charge_pct', 'share_agency_fee_pct', 'share_net_profit_pct', 'share_notes',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_joining' => 'date',
            'date_of_resignation' => 'date',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPartner(): bool
    {
        return $this->user_type === 'partner';
    }
}
