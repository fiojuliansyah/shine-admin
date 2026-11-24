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

    /** "pdf" atau "image" — dipakai modal pratinjau untuk memilih viewer. */
    public function getFileKindAttribute(): string
    {
        $path = strtolower((string) ($this->file_public_id ?: $this->file_url));

        return str_contains($path, '.pdf') ? 'pdf' : 'image';
    }

    /** URL file lewat aplikasi (tanpa membuka link storage). $admin = route manage, selain itu milik sendiri. */
    public function previewUrl(bool $admin = true, bool $download = false): string
    {
        return route($admin ? 'documents.file' : 'applicant.documents.file', array_filter([
            'document' => $this->id,
            'download' => $download ? 1 : null,
        ]));
    }

    /** URL untuk membuka dokumen: link sementara bila file private di S3, selain itu file_url apa adanya. */
    public function getViewUrlAttribute(): ?string
    {
        return FileStorage::displayUrl($this->file_public_id, $this->file_url);
    }
}
