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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
