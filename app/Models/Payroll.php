<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'site_id',
        'pay_type',
        'amount',
        'cutoff_day',
        'bpjs_type',
        'bpjs_base',
        'bpjs_budget_tk',
        'bpjs_budget_kes',
        'jkk_company',
        'jkm_company',
        'jht_company',
        'jp_company',
        'kes_company',
        'jht_employee',
        'jp_employee',
        'kes_employee',
        'pph21_method'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function payroll_components()
    {
        return $this->hasMany(PayrollComponent::class);
    }

    public function payroll_deductions()
    {
        return $this->hasMany(PayrollDeduction::class);
    }

    public function payroll_time_deductions()
    {
        return $this->hasMany(PayrollTimeDeduction::class);
    }

    public function payroll_overtime()
    {
        return $this->hasOne(PayrollOvertime::class);
    }

    public function generatedPayrolls()
    {
        return $this->hasMany(GeneratePayroll::class);
    }
    
}

