<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatrollSession extends Model
{
    protected $guarded = ['id'];

    public function security_patroll()
    {
        return $this->hasMany(SecurityPatroll::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
