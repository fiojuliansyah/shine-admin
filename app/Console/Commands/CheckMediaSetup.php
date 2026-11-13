<?php

namespace App\Console\Commands;

use App\Models\Profile;
use App\Services\FileStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Throwable;

/**
 * Diagnosis kenapa file (mis. foto profil) di object storage tidak tampil.
 * Tidak mengubah apa pun: hanya membaca konfigurasi, bucket, DB, dan mencoba membuka URL.
 */
class CheckMediaSetup extends Command
{
    protected $signature = 'media:check';

    protected $description = 'Cek konfigurasi object storage (S3) dan apakah foto profil terbaru bisa dibuka';

    private int $problems = 0;

    public function handle(): int
    {
        $disk = FileStorage::diskName();
        $config = config("filesystems.disks.{$disk}", []);
        $appUrl = rtrim((string) config('app.url'), '/');
        $awsUrl = (string) ($config['url'] ?? '');

        $this->table(['Pengaturan', 'Nilai'], [
            ['APP_URL', $appUrl],
            ['UPLOAD_DISK', $disk . ' (driver: ' . ($config['driver'] ?? '-') . ')'],
            ['AWS_BUCKET', $config['bucket'] ?? '-'],
            ['AWS_ENDPOINT', $config['endpoint'] ?? '-'],
            ['AWS_USE_PATH_STYLE_ENDPOINT', var_export((bool) ($config['use_path_style_endpoint'] ?? false), true)],
            ['AWS_URL', $awsUrl ?: '(kosong)'],
            ['URL file publik', FileStorage::baseUrl() . '<path>'],
        ]);

        if (($config['driver'] ?? null) !== 's3') {
            return $this->stop("UPLOAD_DISK \"{$disk}\" bukan disk S3.");
        }

        if ($awsUrl === '') {
            $this->problem("AWS_URL kosong: file publik memakai URL S3 langsung, yang ditolak (403) oleh Cloudeka.\n"
                . "  Perbaiki: isi AWS_URL={$appUrl}/media lalu jalankan php artisan optimize:clear");
        } elseif (! str_starts_with($awsUrl, $appUrl . '/media')) {
            $this->warn("! AWS_URL tidak sama dengan {$appUrl}/media. Pastikan memang disengaja (CDN / domain lain).");
        }

        if (str_starts_with($appUrl, 'https://') && str_starts_with($awsUrl, 'http://')) {
            $this->problem('Situs memakai https tetapi AWS_URL http: browser memblokir gambar (mixed content). Ganti AWS_URL ke https.');
        }

        if (! Route::has('media.show')) {
            $this->problem("Rute /media belum terdaftar. Pastikan MediaController & routes/web.php ter-deploy, lalu php artisan route:clear");
        } else {
            $this->info('✓ Rute /media terdaftar');
        }

        try {
            FileStorage::disk()->getClient()->headBucket(['Bucket' => $config['bucket']]);
            $this->info('✓ Koneksi & kredensial bucket OK');
        } catch (Throwable $e) {
            return $this->stop('Tidak bisa mengakses bucket: ' . $e->getMessage());
        }

        $profile = Profile::whereNotNull('avatar_url')->where('avatar_url', '!=', '')->latest('updated_at')->first();

        if (! $profile) {
            $this->warn('! Belum ada foto profil di database untuk dicek.');
        } else {
            $stored = $profile->getRawOriginal('avatar_url');
            $shown = $profile->avatar_url;
            $this->line("Foto profil terbaru (user #{$profile->user_id}):");
            $this->line("  tersimpan di DB : {$stored}");
            $this->line("  ditampilkan     : {$shown}");

            if (str_contains($stored, 'res.cloudinary.com')) {
                $this->problem('Foto ini masih di Cloudinary (akun nonaktif). Jalankan media:migrate-to-s3 setelah akun Cloudinary aktif kembali.');
            } else {
                $this->checkUrl($shown);
            }
        }

        $this->newLine();
        $this->problems === 0
            ? $this->info('Tidak ditemukan masalah konfigurasi.')
            : $this->error("{$this->problems} masalah ditemukan (lihat ✗ di atas).");

        return $this->problems === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function checkUrl(string $url): void
    {
        try {
            $response = Http::timeout(20)->withOptions(['allow_redirects' => ['track_redirects' => true]])->get($url);
            $hops = $response->header('X-Guzzle-Redirect-History');
            $final = $hops ? last(explode(', ', $hops)) : $url;
            $host = parse_url($final, PHP_URL_HOST);

            if ($response->successful()) {
                $this->info("✓ Foto bisa dibuka (HTTP {$response->status()}, " . ($response->header('Content-Type') ?: '-') . ", dari {$host})");
            } else {
                $this->problem("Foto tidak bisa dibuka: HTTP {$response->status()} dari {$host}"
                    . ($response->status() === 404 && str_contains($url, '/media/') ? ' (rute /media tidak ditemukan di server?)' : ''));
            }
        } catch (Throwable $e) {
            $this->problem('Gagal membuka URL foto: ' . $e->getMessage()
                . "\n  (Bila server tidak bisa mengakses domainnya sendiri, buka URL di atas dari browser.)");
        }
    }

    private function problem(string $message): void
    {
        $this->problems++;
        $this->error('✗ ' . $message);
    }

    private function stop(string $message): int
    {
        $this->error('✗ ' . $message);

        return self::FAILURE;
    }
}
