<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratePayroll extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function ptkp_rate()
    {
        return $this->belongsTo(PtkpRate::class);
    }
}
