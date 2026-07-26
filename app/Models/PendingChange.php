<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingChange extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['changes' => 'array', 'reviewed_at' => 'datetime'];

    public function requestedBy() { return $this->belongsTo(User::class, 'requested_by'); }
    public function reviewedBy() { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function subject()
    {
        $class = $this->subject_type;
        return class_exists($class) ? $class::find($this->subject_id) : null;
    }
}
