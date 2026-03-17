<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;


class Applicant extends Model
{
    use HasFactory, Notifiable;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function career()
    {
        return $this->belongsTo(Career::class, 'career_id');
    }

    public function approve()
    {
        return $this->belongsTo(User::class, 'approve_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
    

    public function routeNotificationForWhatsapp()
    {
        return $this->user ? $this->user->phone : null;
    }
}
