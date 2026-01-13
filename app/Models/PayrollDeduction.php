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
        'is_prorate',
        'expired_at'
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function deduction_type()
    {
        return $this->belongsTo(DeductionType::class);
    }
}
