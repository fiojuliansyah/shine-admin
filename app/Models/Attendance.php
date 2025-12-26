<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 'latlong', 'user_id', 'site_id', 'face_image_url_clockin', 'face_image_public_id_clockin',
        'clock_in', 'face_image_url_clockout', 'face_image_public_id_clockout', 'clock_out', 'type',
        'is_reliver', 'backup_id', 'leave_id', 'remark', 'late_duration', 'has_overtime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function overtimes()
    {
        return $this->hasMany(Overtime::class);
    }

    public function minutes()
    {
        return $this->hasMany(Minute::class);
    }

    public function leaves()
    {
        return $this->hasOne(Leave::class);
    }

    public function permits()
    {
        return $this->hasOne(Permit::class);
    }

    protected $casts = [
        'type' => 'string',
        'date' => 'date',
        'clock_in' => 'datetime:H:i',
        'clock_out' => 'datetime:H:i',
    ];
}
