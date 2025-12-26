<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'shift_code', 'clock_in', 'clock_out','type', 'site_id'];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
