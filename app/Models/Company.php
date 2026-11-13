<?php

namespace App\Models;

use App\Services\FileStorage;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logAll(['*']);
    }

    /** Logo yang tersimpan sebagai URL S3 langsung ditampilkan lewat awalan AWS_URL (lihat FileStorage). */
    public function getLogoUrlAttribute($value): ?string
    {
        return FileStorage::normalizeUrl($value);
    }

    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    public function nikConfigs()
    {
        return $this->hasMany(EmployeeNikConfig::class);
    }

    public function defaultNikConfig()
    {
        return $this->hasOne(EmployeeNikConfig::class)->where('is_default', true);
    }
}
