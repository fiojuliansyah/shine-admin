<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Mime\MimeTypes;
use Throwable;

/**
 * Satu pintu untuk menyimpan file upload aplikasi ke object storage (S3).
 *
 * Disk diatur lewat config('filesystems.upload_disk') / env UPLOAD_DISK.
 * Setiap upload mengembalikan ['path' => ..., 'url' => ...]:
 *   - path disimpan di kolom *_public_id (dipakai untuk menghapus file),
 *   - url  disimpan di kolom *_url (dipakai untuk menampilkan file).
 */
class FileStorage
{
    public static function diskName(): string
    {
        return config('filesystems.upload_disk', 's3');
    }

    public static function disk(): FilesystemAdapter
    {
        return Storage::disk(static::diskName());
    }

    /**
     * Upload file dari request.
     *
     * @return array{path: string, url: string}
     */
    public static function upload(UploadedFile $file, string $folder): array
    {
        $path = static::disk()->putFile(
            trim($folder, '/'),
            $file,
            ['ContentType' => $file->getMimeType() ?: 'application/octet-stream']
        );

        if ($path === false) {
            throw new RuntimeException('Gagal mengunggah file ke storage.');
        }

        return static::result($path);
    }

    /**
     * Upload gambar/file dari string base64 (data URI "data:image/png;base64,..." atau base64 polos).
     *
     * @return array{path: string, url: string}
     */
    public static function uploadBase64(string $data, string $folder, ?string $filename = null): array
    {
        $mime = null;

        if (preg_match('/^data:([\w.+\/-]+);base64,/', $data, $matches)) {
            $mime = $matches[1];
            $data = substr($data, strlen($matches[0]));
        }

        $binary = base64_decode(str_replace(' ', '+', $data), true);

        if ($binary === false || $binary === '') {
            throw new InvalidArgumentException('Data base64 tidak valid.');
        }

        $mime ??= (new \finfo(FILEINFO_MIME_TYPE))->buffer($binary) ?: 'application/octet-stream';

        return static::putContents($binary, $folder, $mime, $filename);
    }

    /**
     * Simpan konten mentah. Nama file dibuat acak bila $filename tidak diisi,
     * ekstensi diambil dari MIME type.
     *
     * @return array{path: string, url: string}
     */
    public static function putContents(string $contents, string $folder, string $mime, ?string $filename = null): array
    {
        $extension = MimeTypes::getDefault()->getExtensions($mime)[0] ?? 'bin';
        $path = trim($folder, '/') . '/' . ($filename ?? Str::random(40)) . '.' . $extension;

        if (! static::disk()->put($path, $contents, ['ContentType' => $mime])) {
            throw new RuntimeException('Gagal mengunggah file ke storage.');
        }

        return static::result($path);
    }

    /**
     * Hapus file berdasarkan path yang disimpan di kolom *_public_id.
     * Aman dipanggil dengan null / path yang sudah tidak ada. Error storage
     * dilaporkan ke log tanpa menggagalkan proses utama.
     */
    public static function delete(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        try {
            static::disk()->delete($path);
        } catch (Throwable $e) {
            report($e);
        }
    }

    public static function url(string $path): string
    {
        return static::disk()->url($path);
    }

    /**
     * @return array{path: string, url: string}
     */
    protected static function result(string $path): array
    {
        return ['path' => $path, 'url' => static::url($path)];
    }
}
