<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function backup()
    {
        return $this->belongsTo(User::class, 'backup_id');
    }
}
