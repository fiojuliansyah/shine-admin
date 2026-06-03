<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class EmployeeNikConfig extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'padding'        => 'integer',
        'start_number'   => 'integer',
        'current_number' => 'integer',
        'is_default'     => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public static function defaultForCompany($companyId): ?self
    {
        return self::where('company_id', $companyId)
            ->where('is_default', true)
            ->first()
            ?? self::where('company_id', $companyId)->first();
    }

    /**
     * Generate NIK string and atomically increment the counter.
     *
     * @param  \App\Models\User|null $user
     * @param  string|\DateTimeInterface|null $startDate
     * @return string
     */
    public function generateNik($user = null, $startDate = null): string
    {
        return DB::transaction(function () use ($user, $startDate) {
            $row = self::whereKey($this->id)->lockForUpdate()->first();
            if (!$row) {
                $row = $this;
            }

            $next = max(($row->current_number ?? 0) + 1, $row->start_number ?? 1);
            $row->current_number = $next;
            $row->save();

            return $row->formatNumber($next, $user, $startDate);
        });
    }

    /**
     * Render preview without touching counter.
     */
    public function previewNik(?int $sequence = null, $user = null, $startDate = null): string
    {
        $sequence = $sequence ?? max(($this->current_number ?? 0) + 1, $this->start_number ?? 1);
        return $this->formatNumber($sequence, $user, $startDate);
    }

    public function formatNumber(int $sequence, $user = null, $startDate = null): string
    {
        $padding = $this->padding ?? 5;
        $no = str_pad((string) $sequence, $padding, '0', STR_PAD_LEFT);

        $prefix       = $this->prefix ?? '';
        $kodeJabatan  = 'XX';
        if ($user) {
            $role = method_exists($user, 'roles') ? $user->roles()->first() : null;
            if ($role) {
                $kodeJabatan = strtoupper($role->code ?: substr($role->name ?? 'XX', 0, 3));
            }
        }

        $kodeCompany = 'XX';
        if ($this->relationLoaded('company') ? $this->company : $this->company()->first()) {
            $company = $this->company;
            $kodeCompany = strtoupper($company->unique_id ?? substr($company->name ?? 'XX', 0, 3));
        }

        $date = $startDate ? Carbon::parse($startDate) : Carbon::now();
        $tanggalJoin      = $date->format('d');
        $bulanJoin        = $date->format('m');
        $tahunJoin        = $date->format('Y');
        $tahunJoinPendek  = $date->format('y');

        $search = [
            '{no}',
            '{prefix}',
            '{kode_jabatan}',
            '{kode_company}',
            '{tanggal_join}',
            '{bulan_join}',
            '{tahun_join}',
            '{tahun_join_pendek}',
        ];

        $replace = [
            $no,
            $prefix,
            $kodeJabatan,
            $kodeCompany,
            $tanggalJoin,
            $bulanJoin,
            $tahunJoin,
            $tahunJoinPendek,
        ];

        return str_replace($search, $replace, $this->format ?? '{prefix}{no}');
    }
}
