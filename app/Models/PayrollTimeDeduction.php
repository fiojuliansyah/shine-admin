<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollTimeDeduction extends Model
{
    protected $fillable = [
        'payroll_id',
        'pay_type',
        'type',
        'name',
        'amount',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
