<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Riwayat karier karyawan: mutasi, promosi, demosi, resign, aktif kembali, revisi kontrak.
 *
 * - mutasi        : from_id / to_id = site asal / tujuan
 * - promosi/demosi: from_value / to_value = jabatan lama / baru
 * - kontrak       : from_value / to_value = periode kontrak lama / baru
 */
class Mutation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public const TYPES = [
        'mutasi' => 'Mutasi',
        'promosi' => 'Promosi',
        'demosi' => 'Demosi',
        'resign' => 'Resign',
        'aktif' => 'Aktif Kembali',
        'kontrak' => 'Revisi Kontrak',
    ];

    private const BADGES = [
        'mutasi' => 'bg-info',
        'promosi' => 'bg-success',
        'demosi' => 'bg-warning',
        'resign' => 'bg-danger',
        'aktif' => 'bg-primary',
        'kontrak' => 'bg-secondary',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function from()
    {
        return $this->belongsTo(Site::class, 'from_id', 'id');
    }

    public function to()
    {
        return $this->belongsTo(Site::class, 'to_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type ?? 'mutasi'] ?? ucfirst((string) $this->type);
    }

    public function getBadgeClassAttribute(): string
    {
        return self::BADGES[$this->type ?? 'mutasi'] ?? 'bg-secondary';
    }

    /** Teks kolom "Dari" sesuai jenis riwayat. */
    public function getFromLabelAttribute(): string
    {
        return match ($this->type ?? 'mutasi') {
            'mutasi' => $this->from?->name ?? '-',
            default => $this->from_value ?: '-',
        };
    }

    /** Teks kolom "Ke" sesuai jenis riwayat. */
    public function getToLabelAttribute(): string
    {
        return match ($this->type ?? 'mutasi') {
            'mutasi' => $this->to?->name ?? '-',
            default => $this->to_value ?: '-',
        };
    }
}
