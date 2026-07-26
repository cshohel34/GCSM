<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Rank extends Model {
    protected $guarded = ['id'];
    public function crew() { return $this->hasMany(CrewProfile::class, 'current_rank_id'); }
}
