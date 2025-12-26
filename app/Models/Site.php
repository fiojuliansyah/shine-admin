<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Site extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll(['*']);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function users_leader()
    {
        return $this->belongsToMany(User::class, 'user_has_sites', 'site_id', 'user_id');
    }

    public function taskPlanners(){
        return $this->hasMany(TaskPlanner::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function floors()
    {
        return $this->hasMany(Floor::class);
    }

    public function patrolls()
    {
        return $this->hasMany(PatrollSession::class);
    }

    public function securityPatrolls()
    {
        return $this->hasMany(SecurityPatroll::class);
    }
}
