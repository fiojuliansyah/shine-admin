<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Letter extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = [];

    protected $casts = [
        'number_padding' => 'integer',
        'require_hrd_signature' => 'boolean',
        'require_employee_signature' => 'boolean',
    ];

    public function numberConfig()
    {
        return $this->belongsTo(LetterNumberConfig::class, 'letter_number_config_id');
    }

    public function generateLetterNumber(int $sequence, $site = null, $user = null): string
    {
        $config = $this->numberConfig;
        $kode_tipe = $this->type ? strtoupper($this->type->code ?? substr($this->type->name, 0, 3)) : 'XX';
        
        if ($config) {
            // Tambahkan parameter letter ke generateNumber agar bisa mengakses kode_tipe
            return $this->numberConfig->generateNumber($sequence, $site, $user, $this);
        }
        $format = $this->number_format ?? '{no}/{kode_tipe}/{romawi}/{tahun}';
        $padding = $this->number_padding ?? 3;
        $no = str_pad($sequence, $padding, '0', STR_PAD_LEFT);
        $romawi = $this->_toRomawi(now()->month);
        $tahun = now()->year;
        $tahun_pendek = substr((string) now()->year, -2);
        $bulan = str_pad(now()->month, 2, '0', STR_PAD_LEFT);
        $kode_site = $site ? ($site->unique_id ?? strtoupper(substr($site->name, 0, 3))) : 'XX';
        $kode_company = $site && $site->company ? ($site->company->unique_id ?? strtoupper(substr($site->company->name, 0, 3))) : 'XX';
        $kode_jabatan = $user ? strtoupper($user->roles()->first()->code ?? substr($user->roles()->first()->name ?? 'XX', 0, 3)) : 'XX';
        $prefix = $this->number_prefix ?? '';
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logAll(['*']);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function type()
    {
        return $this->belongsTo(TypeLetter::class, 'type_letter_id');
    }

    public function customVariables()
    {
        return $this->hasMany(CustomVariable::class);
    }
}
