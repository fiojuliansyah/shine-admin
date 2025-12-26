<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayrollDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'pay_type',
        'deduction_type_id',
        'name',
        'amount',
        'percentage',
        'is_prorate'
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
