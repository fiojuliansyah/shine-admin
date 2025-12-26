<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jobdesk extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }
}
