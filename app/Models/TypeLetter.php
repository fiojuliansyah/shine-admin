<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TypeLetter extends Model
{
    protected $guarded = [];

    public function letters()
    {
        return $this->hasMany(Letter::class);
    }

    public function generates()
    {
        return $this->hasManyThrough(
            Generate::class,
            Letter::class,
            'type_letter_id',
            'letter_id',
            'id',
            'id'
        );
    }
}
