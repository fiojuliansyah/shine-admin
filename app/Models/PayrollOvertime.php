<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollOvertime extends Model
{
    protected $fillable = [
        'payroll_id',
        'pay_type',
        'amount'
    ];

    // Relasi ke Payroll
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
