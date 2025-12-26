<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FindingsReport extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
