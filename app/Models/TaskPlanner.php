<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskPlanner extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function taskProgress()
    {
        return $this->hasOne(TaskProgress::class);
    }

    public function security()
    {
        return $this->hasMany(SecurityPatroll::class);
        // return $this->hasOne(SecurityPatroll::class);
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class, 'floor_id');
    }
}
