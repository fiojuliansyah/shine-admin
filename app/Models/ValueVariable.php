<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValueVariable extends Model
{
    protected $fillable = [
        'generate_id',
        'custom_variable_id',
        'value',
    ];

    public function customVariable()
    {
        return $this->belongsTo(CustomVariable::class);
    }

    public function generate()
    {
        return $this->belongsTo(Generate::class);
    }


}
