<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip_progress extends Model
{
    protected $guarded = ['id'];

    // relasi
    public function businessTrip()
    {
        return $this->belongsTo(Business_trips::class);
    }
}
