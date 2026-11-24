<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TypeLetter extends Model
{
    protected $guarded = [];

    /**
     * Ambil nomor urut berikutnya (penghitung cadangan untuk template tanpa konfigurasi nomor).
     * Baris dikunci (lockForUpdate) agar dua proses bersamaan tidak mendapat nomor yang sama.
     */
    public function nextNumber(int $start = 1): int
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($start) {
            $row = static::whereKey($this->getKey())->lockForUpdate()->first();
            $next = max((int) ($row?->number ?? 0) + 1, $start);
            static::whereKey($this->getKey())->update(['number' => $next]);
            $this->number = $next;

            return $next;
        });
    }

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
