<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'password',
        'site_id',
        'leader_id',
        'nik',
        'phone',
        'employee_nik',
        'department_id',
        'is_employee',
        'profile_qr'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable();
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function payroll()
    {
        return $this->hasOne(Payroll::class);
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    // Relasi bawahan (subordinates)
    public function subordinates()
    {
        return $this->hasMany(User::class, 'leader_id');
    }
    
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    // pivot supervisor
    public function sites_leader()
    {
        return $this->belongsToMany(Site::class, 'user_has_sites', 'user_id', 'site_id')
            ->withTimestamps();
    }

    // Optional helper supaya langsung dapat semua task
    public function allTaskPlanners()
    {
        return TaskPlanner::whereIn('site_id', $this->sites_leader->pluck('id'))->get();
    }

    public function document()
    {
        return $this->hasMany(Document::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function notificationSettings()
    {
        return $this->hasOne(UserNotification::class);
    }
}
