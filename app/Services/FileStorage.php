<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;
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

    /** Sisi terpanjang maksimum (px) gambar per folder; gambar yang lebih besar diperkecil. */
    private const IMAGE_MAX_SIDE = [
        'Documents' => 2000, // scan KTP / KK / SKCK harus tetap terbaca jelas
        'avatars' => 1024,
        'faces' => 1024,
        'companies_logo' => 1000,
        'esign_images' => 1000,
    ];

    private const IMAGE_DEFAULT_MAX_SIDE = 1600;

    /** Kualitas JPEG / WebP (0-100). */
    private const IMAGE_QUALITY = ['Documents' => 85];

    private const IMAGE_DEFAULT_QUALITY = 80;

    /** Gambar di atas jumlah piksel ini tidak diproses (menghindari kehabisan memori PHP). */
    private const IMAGE_MAX_PIXELS = 40_000_000;

    private const COMPRESSIBLE_IMAGES = ['image/jpeg', 'image/png', 'image/webp'];

    /** Jenis file yang boleh disimpan. Selain ini (HTML, SVG, script, dst.) ditolak agar tidak bisa dipakai XSS. */
    private const ALLOWED_IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    /** Folder yang juga menerima PDF. */
    private const PDF_FOLDERS = ['Documents'];

    private static ?string $baseUrl = null;

    /** @var string[]|null */
    private static ?array $storageBaseUrls = null;

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
        $mime = $file->getMimeType() ?: 'application/octet-stream'; // dideteksi dari isi file
        static::assertAllowedMime($mime, $folder);

        // Gambar dikompres dulu (lihat compressImage); file lain disimpan apa adanya.
        if (in_array($mime, self::COMPRESSIBLE_IMAGES, true)) {
            return static::putContents((string) $file->get(), $folder, $mime);
        }

        $path = static::disk()->putFile(
            trim($folder, '/'),
            $file,
            static::writeOptions($folder, $mime)
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

        // Jangan percaya MIME yang ditulis di data URI; deteksi dari isi file.
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($binary) ?: 'application/octet-stream';
        static::assertAllowedMime($mime, $folder);

        return static::putContents($binary, $folder, $mime, $filename);
    }

    /**
     * Simpan konten mentah. Gambar dikompres lebih dulu. Nama file dibuat acak bila
     * $filename tidak diisi, ekstensi diambil dari MIME type (hasil kompresi).
     *
     * @return array{path: string, url: string}
     */
    public static function putContents(string $contents, string $folder, string $mime, ?string $filename = null): array
    {
        static::assertAllowedMime((new \finfo(FILEINFO_MIME_TYPE))->buffer($contents) ?: $mime, $folder);

        if ($compressed = static::compressImage($contents, $mime, $folder)) {
            [$contents, $mime] = $compressed;
        }

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

    /**
     * Perkecil & kompres gambar sebelum disimpan:
     * - diperkecil bila sisi terpanjang melebihi batas folder (rasio tetap, tidak pernah diperbesar),
     * - dirotasi sesuai EXIF, metadata (EXIF/GPS) dibuang,
     * - JPEG/WebP di-encode ulang; PNG tanpa transparansi diubah ke JPEG, PNG transparan tetap PNG.
     *
     * Mengembalikan null (simpan file asli) bila bukan gambar yang didukung, terlalu besar
     * untuk diproses, gagal diproses, atau hasilnya tidak lebih kecil dari aslinya.
     *
     * @return array{0: string, 1: string}|null [isi baru, mime baru]
     */
    public static function compressImage(string $contents, string $mime, string $folder): ?array
    {
        if (! in_array($mime, self::COMPRESSIBLE_IMAGES, true)) {
            return null;
        }

        $size = @getimagesizefromstring($contents);
        if (! $size || $size[0] * $size[1] > self::IMAGE_MAX_PIXELS || ! static::ensureMemoryFor($size[0], $size[1])) {
            return null;
        }

        $top = Str::before(trim($folder, '/'), '/');
        $maxSide = self::IMAGE_MAX_SIDE[$top] ?? self::IMAGE_DEFAULT_MAX_SIDE;
        $quality = self::IMAGE_QUALITY[$top] ?? self::IMAGE_DEFAULT_QUALITY;

        try {
            $image = Image::read($contents)->scaleDown($maxSide, $maxSide);

            [$encoder, $targetMime] = match (true) {
                $mime === 'image/webp' => [new WebpEncoder($quality, strip: true), 'image/webp'],
                $mime === 'image/png' && static::pngHasTransparency($contents) => [new PngEncoder, 'image/png'],
                default => [new JpegEncoder($quality, progressive: true, strip: true), 'image/jpeg'],
            };

            $output = (string) $image->encode($encoder);
        } catch (Throwable $e) {
            report($e);

            return null;
        }

        return strlen($output) < strlen($contents) ? [$output, $targetMime] : null;
    }

    /**
     * GD memuat gambar utuh ke memori (~5 byte/piksel). Kehabisan memori adalah fatal error yang
     * tidak bisa ditangkap, jadi pastikan cukup dulu: naikkan memory_limit sementara bila perlu
     * (maks. 512M); bila tetap tidak cukup, gambar disimpan tanpa kompresi.
     */
    private static function ensureMemoryFor(int $width, int $height): bool
    {
        $limit = static::toBytes((string) ini_get('memory_limit'));
        if ($limit <= 0) {
            return true; // tanpa batas
        }

        $needed = memory_get_usage(true) + $width * $height * 5 + 16 * 1024 * 1024;
        if ($needed <= $limit) {
            return true;
        }

        $max = 512 * 1024 * 1024;

        return $needed <= $max && ini_set('memory_limit', (string) (int) ceil($needed / 1048576) . 'M') !== false;
    }

    private static function toBytes(string $value): int
    {
        $value = trim($value);
        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }

    /** PNG dengan kanal alpha (RGBA / gray+alpha) atau chunk tRNS dianggap transparan. */
    private static function pngHasTransparency(string $contents): bool
    {
        if (in_array(ord($contents[25] ?? "\0"), [4, 6], true)) {
            return true;
        }

        $trns = strpos($contents, 'tRNS');
        $idat = strpos($contents, 'IDAT');

        return $trns !== false && ($idat === false || $trns < $idat);
    }

    /** Tolak file yang bukan gambar (atau PDF untuk folder dokumen). */
    public static function assertAllowedMime(string $mime, string $folder): void
    {
        $mime = strtolower(trim($mime));
        $allowed = self::ALLOWED_IMAGE_MIMES;
        if (in_array(trim($folder, '/'), self::PDF_FOLDERS, true)) {
            $allowed[] = 'application/pdf';
        }

        if (! in_array($mime, $allowed, true)) {
            throw new InvalidArgumentException('Jenis file tidak diizinkan (' . $mime . '). Gunakan gambar JPG/PNG/WebP' .
                (in_array(trim($folder, '/'), self::PDF_FOLDERS, true) ? ' atau PDF.' : '.'));
        }
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
        if (static::$storageBaseUrls !== null) {
            return static::$storageBaseUrls;
        }

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

        return static::$storageBaseUrls = array_values(array_unique($bases));
    }

    /**
     * URL file publik yang tersimpan di DB, ditulis ulang ke awalan AWS_URL sekarang bila
     * menunjuk ke bucket ini (mis. tersimpan sebagai URL S3 langsung sebelum AWS_URL diisi).
     * URL lain (Cloudinary, eksternal) dikembalikan apa adanya.
     */
    public static function normalizeUrl(?string $url): ?string
    {
        $path = static::pathFromUrl($url);

        return $path === null ? $url : static::url($path);
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
