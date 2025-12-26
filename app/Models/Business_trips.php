<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business_trips extends Model
{
    protected $guarded = ['id'];

    // relasi
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function site()
    {
        return $this->belongsTo(Site::class);
    }
    public function trip_progress()
    {
        return $this->hasOne(trip_progress::class, 'business_trip_id');
    }
}
