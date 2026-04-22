<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomVariable extends Model
{
    protected $fillable = [
        'letter_id',
        'name',
        'variable',
    ];

    public function values()
    {
        return $this->hasMany(ValueVariable::class);
    }
}
