<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'interest_rate',
        'tenor',
        'monthly_installment',
        'remaining_balance',
        'status',
        'start_date',
        'due_date'
    ];

    protected $casts = [
        'tenor' => 'integer',
        'amount' => 'float',
        'interest_rate' => 'float',
        'remaining_balance' => 'float',
        'monthly_installment' => 'decimal:2',
        'start_date' => 'date',
        'due_date' => 'date'
    ];

    public function payments()
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}