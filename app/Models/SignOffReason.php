<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignOffReason extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'note_required' => 'boolean',
        'active' => 'boolean',
    ];
}
