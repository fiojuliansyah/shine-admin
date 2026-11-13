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
    /** Folder yang boleh dibaca publik lewat URL (lihat command media:s3-public-policy). */
    public const PUBLIC_FOLDERS = ['avatars', 'faces', 'companies_logo', 'letter-images'];

    /** Folder berisi data pribadi (KTP, KK, SKCK, e-sign): tidak publik, dibuka lewat link sementara. */
    public const PRIVATE_FOLDERS = ['Documents', 'esign_images'];

    private static ?string $baseUrl = null;

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
            static::writeOptions($folder, $file->getMimeType() ?: 'application/octet-stream')
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

        if (! static::disk()->put($path, $contents, static::writeOptions($path, $mime))) {
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

    public static function isPrivate(?string $path): bool
    {
        return filled($path) && in_array(Str::before(ltrim($path, '/'), '/'), self::PRIVATE_FOLDERS, true);
    }

    /** Opsi tulis: file di folder private diunggah dengan ACL private. */
    public static function writeOptions(string $path, string $mime): array
    {
        $options = ['ContentType' => $mime];

        if (static::isPrivate($path)) {
            $options['visibility'] = 'private';
        }

        return $options;
    }

    /** Awalan URL file di disk upload, untuk membedakan file S3 dari URL lama (Cloudinary). */
    public static function baseUrl(): string
    {
        return static::$baseUrl ??= Str::beforeLast(static::url('__probe__'), '__probe__');
    }

    /**
     * Semua awalan URL yang menunjuk ke bucket ini: AWS_URL (bila diisi) serta URL langsung
     * S3 gaya virtual-host & path-style (bisa tersimpan sebelum AWS_URL diubah).
     *
     * @return string[]
     */
    public static function storageBaseUrls(): array
    {
        $bases = [static::baseUrl()];
        $config = config('filesystems.disks.' . static::diskName());

        if (($config['driver'] ?? null) === 's3' && filled($config['bucket'] ?? null)) {
            $bucket = $config['bucket'];

            if (filled($config['endpoint'] ?? null)) {
                $scheme = parse_url($config['endpoint'], PHP_URL_SCHEME) ?: 'https';
                $host = parse_url($config['endpoint'], PHP_URL_HOST);
                $bases[] = "{$scheme}://{$bucket}.{$host}/";
                $bases[] = "{$scheme}://{$host}/{$bucket}/";
            } elseif (static::disk() instanceof \Illuminate\Filesystem\AwsS3V3Adapter) {
                $bases[] = Str::beforeLast(static::disk()->getClient()->getObjectUrl($bucket, '__probe__'), '__probe__');
            }
        }

        return array_values(array_unique($bases));
    }

    /** Path file di bucket bila $url menunjuk ke bucket ini, selain itu null. */
    public static function pathFromUrl(?string $url): ?string
    {
        foreach (blank($url) ? [] : static::storageBaseUrls() as $base) {
            if (str_starts_with($url, $base)) {
                return rawurldecode(Str::before(substr($url, strlen($base)), '?'));
            }
        }

        return null;
    }

    /**
     * URL untuk ditampilkan ke pengguna.
     *
     * File private di S3 (dokumen, e-sign) mendapat link sementara; file publik atau
     * URL lama (Cloudinary / sumber lain) dikembalikan apa adanya.
     */
    public static function displayUrl(?string $path, ?string $storedUrl = null, int $minutes = 60): ?string
    {
        $onDisk = blank($storedUrl) || static::pathFromUrl($storedUrl) !== null;

        if ($onDisk && static::isPrivate($path) && static::disk()->providesTemporaryUrls()) {
            try {
                return static::disk()->temporaryUrl($path, now()->addMinutes($minutes));
            } catch (Throwable $e) {
                report($e);
            }
        }

        return $storedUrl ?: (filled($path) ? static::url($path) : null);
    }

    /**
     * @return array{path: string, url: string}
     */
    protected static function result(string $path): array
    {
        return ['path' => $path, 'url' => static::url($path)];
    }
}
