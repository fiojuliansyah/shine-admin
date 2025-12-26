<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityPatroll extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
    
    public function taskPlanner()
    {
        return $this->belongsTo(TaskPlanner::class);
    }

    public function patroll()
    {
        return $this->belongsTo(PatrollSession::class, 'patroll_session_id');
    }
}
