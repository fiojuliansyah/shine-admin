<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'pay_type',
        'component_type_id',
        'name',
        'amount',
        'percentage',
        'is_prorate'
    ];

    // Relasi ke Payroll
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function component_type()
    {
        return $this->belongsTo(ComponentType::class);
    }

}
