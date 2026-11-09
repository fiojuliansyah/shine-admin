<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Profile extends Model
{
    use HasFactory, LogsActivity;
    
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logAll(['*']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Karyawan dianggap resign bila punya tanggal resign atau berstatus RESIGN (hasil import / proses resign). */
    public function isResigned(): bool
    {
        return filled($this->resign_date) || $this->employment_status === 'RESIGN';
    }

    /** Kondisi query yang sama dengan isResigned(), untuk whereHas / whereDoesntHave pada relasi profile. */
    public static function resignedConstraint(): \Closure
    {
        return fn ($query) => $query->where(fn ($q) => $q
            ->where(fn ($d) => $d->whereNotNull('resign_date')->where('resign_date', '!=', ''))
            ->orWhere('employment_status', 'RESIGN'));
    }
}
