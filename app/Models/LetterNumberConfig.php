<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LetterNumberConfig extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function letters()
    {
        return $this->hasMany(Letter::class);
    }

    public function generateNumber(int $sequence, $site = null, $user = null): string
    {
        $format = $this->format ?? '{no}/{romawi}/{tahun}';
        $padding = $this->padding ?? 3;
        $no = str_pad($sequence, $padding, '0', STR_PAD_LEFT);
        $romawi = $this->_toRomawi(now()->month);
        $tahun = now()->year;
        $tahun_pendek = substr((string) now()->year, -2);
        $bulan = str_pad(now()->month, 2, '0', STR_PAD_LEFT);
        $kode_site = $site ? ($site->unique_id ?? strtoupper(substr($site->name, 0, 3))) : 'XX';
        $kode_tipe = 'XX';
        $kode_company = $site && $site->company ? ($site->company->unique_id ?? strtoupper(substr($site->company->name, 0, 3))) : 'XX';
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
