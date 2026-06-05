<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class LetterNumberConfig extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'padding'        => 'integer',
        'start_number'   => 'integer',
        'current_number' => 'integer',
    ];

    public function letters()
    {
        return $this->hasMany(Letter::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function sharedCounter()
    {
        return $this->belongsTo(LetterNumberConfig::class, 'shared_counter_id');
    }

    public function sharedBy()
    {
        return $this->hasMany(LetterNumberConfig::class, 'shared_counter_id');
    }

    public function generateNumber(int $sequence = null, $site = null, $user = null, $letter = null): string
    {
        if ($sequence === null) {
            $sequence = $this->nextSequence();
        }

        return $this->formatNumber($sequence, $site, $user, $letter);
    }

    public function nextSequence(): int
    {
        return DB::transaction(function () {
            $counter = $this->shared_counter_id
                ? self::whereKey($this->shared_counter_id)->lockForUpdate()->first()
                : self::whereKey($this->id)->lockForUpdate()->first();

            if (!$counter) {
                $counter = $this;
            }

            $next = max(($counter->current_number ?? 0) + 1, $counter->start_number ?? 1);
            $counter->current_number = $next;
            $counter->save();

            return $next;
        });
    }

    public function previewNumber($site = null, $user = null, $letter = null): string
    {
        $counter = $this->shared_counter_id
            ? ($this->sharedCounter ?? $this)
            : $this;

        $sequence = max(($counter->current_number ?? 0) + 1, $counter->start_number ?? 1);
        return $this->formatNumber($sequence, $site, $user, $letter);
    }

    public function formatNumber(int $sequence, $site = null, $user = null, $letter = null): string
    {
        $format = $this->format ?? '{no}/{romawi}/{tahun}';
        $padding = $this->padding ?? 3;
        $no = str_pad($sequence, $padding, '0', STR_PAD_LEFT);
        $romawi = $this->_toRomawi(now()->month);
        $tahun = now()->year;
        $tahun_pendek = substr((string) now()->year, -2);
        $bulan = str_pad(now()->month, 2, '0', STR_PAD_LEFT);
        $kode_site = $site ? ($site->unique_id ?? strtoupper(substr($site->name, 0, 3))) : 'XX';
        $kode_tipe = $letter && $letter->type ? strtoupper($letter->type->code ?? substr($letter->type->name, 0, 3)) : 'XX';

        if ($this->relationLoaded('company') ? $this->company : $this->company()->first()) {
            $kode_company = $this->company->unique_id ?? strtoupper(substr($this->company->name, 0, 3));
        } else {
            $kode_company = $site && $site->company ? ($site->company->unique_id ?? strtoupper(substr($site->company->name, 0, 3))) : 'XX';
        }

        $kode_jabatan = $user ? strtoupper($user->roles()->first()->code ?? substr($user->roles()->first()->name ?? 'XX', 0, 3)) : 'XX';
        $prefix = $this->prefix ?? '';

        return str_replace(
            ['{no}', '{romawi}', '{tahun}', '{tahun_pendek}', '{bulan}', '{kode_site}', '{kode_tipe}', '{kode_company}', '{kode_jabatan}', '{prefix}'],
            [$no, $romawi, $tahun, $tahun_pendek, $bulan, $kode_site, $kode_tipe, $kode_company, $kode_jabatan, $prefix],
            $format
        );
    }

    private function _toRomawi(int $month): string
    {
        $map = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        return $map[$month - 1] ?? 'I';
    }
}
