<?php

namespace App\Models;

use App\Services\FileStorage;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
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

    /** URL untuk membuka dokumen: link sementara bila file private di S3, selain itu file_url apa adanya. */
    public function getViewUrlAttribute(): ?string
    {
        return FileStorage::displayUrl($this->file_public_id, $this->file_url);
    }
}
