<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Profile extends Model
{
    use HasFactory, LogsActivity;
    
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logAll(['*']);
    }

    protected $table = 'profiles';
    protected $fillable = [
        'user_id',
        'avatar_url',
        'avatar_public_id',
        'face_id',
        'face_descriptor',
        'esign',
        'employee_nik',
        'gender',
        'birth_place',
        'birth_date',
        'mother_name',
        'number_of_children',
        'npwp_number',
        'marriage_status',
        'address',
        'employee_status',
        'join_date',
        'resign_date',
        'bank_name',
        'account_name',
        'account_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
