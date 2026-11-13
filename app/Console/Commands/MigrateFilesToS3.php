<?php

namespace App\Console\Commands;

use App\Services\FileStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;
use Throwable;

/**
 * Menyalin file lama (Cloudinary / storage lokal "public") ke disk upload (S3)
 * lalu memperbarui URL + path di database.
 *
 * Aman dijalankan berulang: baris yang URL-nya sudah mengarah ke disk tujuan dilewati.
 * File asal TIDAK dihapus, sehingga Cloudinary / storage lokal tetap bisa jadi cadangan.
 */
class MigrateFilesToS3 extends Command
{
    protected $signature = 'media:migrate-to-s3
        {--only=* : Batasi target: companies, avatars, documents, esign, letters}
        {--dry-run : Tampilkan file yang akan dipindah tanpa mengubah apa pun}';

    protected $description = 'Salin file lama (Cloudinary / storage lokal) ke S3 dan perbarui URL di database';

    /** Tabel dengan pasangan kolom URL + path. */
    protected const COLUMN_TARGETS = [
        'companies' => ['table' => 'companies', 'url' => 'logo_url', 'path' => 'logo_public_id', 'folder' => 'companies_logo'],
        'avatars' => ['table' => 'profiles', 'url' => 'avatar_url', 'path' => 'avatar_public_id', 'folder' => 'avatars'],
        'documents' => ['table' => 'documents', 'url' => 'file_url', 'path' => 'file_public_id', 'folder' => 'Documents'],
    ];

    /** Tabel yang isi surat (HTML / JSON) bisa memuat URL gambar /storage/letter-images/... */
    protected const LETTER_TABLES = ['letters', 'generates'];

    /** URL gambar surat lokal, termasuk bentuk JSON-escaped (https:\/\/host\/storage\/...). */
    protected const LETTER_IMAGE_PATTERN = '~(?:https?:(?:\\\\?/){2}[^\s"\'<>]*?)?\\\\?/storage\\\\?/letter-images\\\\?/([A-Za-z0-9_.-]+)~';

    protected bool $dryRun = false;

    protected string $diskBaseUrl = '';

    protected int $migrated = 0;

    protected int $failed = 0;

    /** URL yang sudah di bucket ini tapi berawalan lama (mis. sebelum AWS_URL diubah). */
    protected int $rebased = 0;

    /** @var array<string, string|null> URL lama => URL baru (null bila gagal) */
    protected array $copied = [];

    public function handle(): int
    {
        $disk = FileStorage::diskName();

        if (config("filesystems.disks.{$disk}.driver") !== 's3') {
            $this->error("UPLOAD_DISK saat ini \"{$disk}\", bukan disk S3. Set UPLOAD_DISK=s3 dan isi kredensial AWS_* di .env.");

            return self::FAILURE;
        }

        $all = [...array_keys(self::COLUMN_TARGETS), 'esign', 'letters'];
        $only = $this->option('only');
        $unknown = array_diff($only, $all);

        if ($unknown) {
            $this->error('Target tidak dikenal: ' . implode(', ', $unknown) . '. Pilihan: ' . implode(', ', $all));

            return self::FAILURE;
        }

        // Instance command bisa dipakai ulang (Artisan::call berulang), jadi state selalu di-reset.
        $this->migrated = 0;
        $this->failed = 0;
        $this->rebased = 0;
        $this->copied = [];
        $this->dryRun = (bool) $this->option('dry-run');
        $this->diskBaseUrl = Str::beforeLast(FileStorage::url('__probe__'), '__probe__');

        $this->info("Disk tujuan: {$disk} ({$this->diskBaseUrl})" . ($this->dryRun ? ' [DRY RUN]' : ''));

        $aborted = false;

        try {
            foreach ($only ?: $all as $target) {
                $this->newLine();
                $this->comment("== {$target} ==");

                match ($target) {
                    'esign' => $this->migrateEsign(),
                    'letters' => $this->migrateLetterImages(),
                    default => $this->migrateColumn(self::COLUMN_TARGETS[$target]),
                };
            }
        } catch (MigrationAborted $e) {
            $aborted = true;
            $this->newLine();
            $this->error('DIHENTIKAN: ' . $e->getMessage());
            $this->line('Aktifkan kembali akun sumber (mis. Cloudinary) lalu jalankan ulang command ini; file yang sudah dipindah tidak diulang.');
        }

        $this->newLine();
        $this->table(['Hasil', 'Jumlah'], [
            [$this->dryRun ? 'Akan dipindah' : 'Berhasil dipindah', $this->migrated],
            [$this->dryRun ? 'URL akan diperbarui (tanpa salin)' : 'URL diperbarui (tanpa salin)', $this->rebased],
            ['Gagal', $this->failed],
        ]);

        return $aborted || $this->failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @param  array{table: string, url: string, path: string, folder: string}  $target
     */
    protected function migrateColumn(array $target): void
    {
        DB::table($target['table'])
            ->whereNotNull($target['url'])
            ->where($target['url'], '!=', '')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($target) {
                foreach ($rows as $row) {
                    $url = $row->{$target['url']};
                    $stored = $this->resolve($url, $target['folder'], "{$target['table']}#{$row->id}");

                    if ($stored) {
                        DB::table($target['table'])->where('id', $row->id)->update([
                            $target['url'] => $stored['url'],
                            $target['path'] => $stored['path'],
                        ]);
                    }
                }
            });
    }

    /** profiles.esign dari API berformat JSON {"esign_url": ..., "esign_public_id": ...}. */
    protected function migrateEsign(): void
    {
        DB::table('profiles')
            ->where('esign', 'like', '{%')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $esign = json_decode($row->esign, true);
                    $url = is_array($esign) ? ($esign['esign_url'] ?? null) : null;

                    if (blank($url)) {
                        continue;
                    }

                    $stored = $this->resolve($url, 'esign_images', "profiles#{$row->id} esign");

                    if ($stored) {
                        $esign['esign_url'] = $stored['url'];
                        $esign['esign_public_id'] = $stored['path'];

                        DB::table('profiles')->where('id', $row->id)->update(['esign' => json_encode($esign)]);
                    }
                }
            });
    }

    /** Gambar yang di-upload lewat editor surat tertanam sebagai URL di kolom description. */
    protected function migrateLetterImages(): void
    {
        foreach (self::LETTER_TABLES as $table) {
            DB::table($table)
                ->where('description', 'like', '%letter-images%')
                ->orderBy('id')
                ->chunkById(50, function ($rows) use ($table) {
                    foreach ($rows as $row) {
                        $changed = false;
                        $description = $this->rebaseLetterImages($row->description, $changed, "{$table}#{$row->id}");

                        $description = preg_replace_callback(self::LETTER_IMAGE_PATTERN, function ($match) use ($table, $row, &$changed) {
                            $escaped = str_contains($match[0], '\\/');
                            $url = str_replace('\\/', '/', $match[0]);

                            if ($this->isOnTargetDisk($url)) {
                                return $match[0];
                            }

                            $newUrl = $this->copyToDisk($url, 'letter-images', "{$table}#{$row->id}")['url'] ?? null;

                            if ($newUrl === null) {
                                return $match[0];
                            }

                            $changed = true;

                            return $escaped ? str_replace('/', '\\/', $newUrl) : $newUrl;
                        }, $description);

                        if ($changed && $description !== null) {
                            DB::table($table)->where('id', $row->id)->update(['description' => $description]);
                        }
                    }
                });
        }
    }

    /**
     * File yang sudah ada di bucket ini cukup diperbarui URL-nya; selain itu disalin.
     * Mengembalikan null bila tidak ada perubahan (sudah benar / dry-run / gagal).
     *
     * @return array{path: string, url: string}|null
     */
    protected function resolve(string $url, string $folder, string $label): ?array
    {
        $path = FileStorage::pathFromUrl($url);

        if ($path === null) {
            return $this->copyToDisk($url, $folder, $label);
        }

        $current = FileStorage::url($path);
        if ($url === $current) {
            return null;
        }

        $this->rebased++;
        $this->line("  {$label}: URL diperbarui -> {$current}", null, $this->dryRun ? null : 'v');

        return $this->dryRun ? null : ['path' => $path, 'url' => $current];
    }

    /** Ganti awalan URL bucket lama pada gambar surat (termasuk bentuk JSON-escaped) ke awalan sekarang. */
    protected function rebaseLetterImages(string $description, bool &$changed, string $label): string
    {
        $target = FileStorage::baseUrl() . 'letter-images/';

        foreach (FileStorage::storageBaseUrls() as $base) {
            $old = $base . 'letter-images/';
            if ($old === $target) {
                continue;
            }

            foreach ([[$old, $target], [str_replace('/', '\\/', $old), str_replace('/', '\\/', $target)]] as [$from, $to]) {
                $count = substr_count($description, $from);
                if ($count === 0) {
                    continue;
                }

                $this->rebased += $count;
                $this->line("  {$label}: {$count} URL gambar surat diperbarui", null, $this->dryRun ? null : 'v');

                if (! $this->dryRun) {
                    $description = str_replace($from, $to, $description);
                    $changed = true;
                }
            }
        }

        return $description;
    }

    /**
     * Salin satu file ke disk tujuan. Mengembalikan null saat dry-run atau gagal.
     *
     * @return array{path: string, url: string}|null
     */
    protected function copyToDisk(string $url, string $folder, string $label): ?array
    {
        $key = $this->targetKey($url, $folder);

        if (array_key_exists($url, $this->copied)) {
            return $this->copied[$url] ? ['path' => $this->copied[$url], 'url' => FileStorage::url($this->copied[$url])] : null;
        }

        if ($this->dryRun) {
            $this->migrated++;
            $this->line("  {$label}: {$url} -> {$key}");

            return $this->copied[$url] = null;
        }

        try {
            [$contents, $mime] = $this->fetch($url);

            if (pathinfo($key, PATHINFO_EXTENSION) === '') {
                $key .= '.' . (MimeTypes::getDefault()->getExtensions($mime)[0] ?? 'bin');
            }

            if (! FileStorage::disk()->put($key, $contents, FileStorage::writeOptions($key, $mime))) {
                throw new \RuntimeException('upload ditolak oleh storage');
            }
        } catch (Throwable $e) {
            // Akun sumber dinonaktifkan (mis. "cloud_name ... is disabled"): semua file berikutnya
            // pasti gagal juga, jadi hentikan daripada mencoba ribuan file satu per satu.
            if ($e instanceof RequestException && $this->sourceDisabled($e->response)) {
                throw new MigrationAborted(
                    'sumber file menolak akses (' . Str::limit(Str::squish($e->response->header('X-Cld-Error') ?: $e->response->body()), 150) . ") saat mengambil {$url}",
                    previous: $e
                );
            }

            $this->failed++;
            $this->warn("  GAGAL {$label}: {$url} (" . Str::limit(Str::squish($e->getMessage()), 200) . ')');

            return $this->copied[$url] = null;
        }

        $this->migrated++;
        $this->line("  {$label}: {$key}", null, 'v');
        $this->copied[$url] = $key;

        return ['path' => $key, 'url' => FileStorage::url($key)];
    }

    /**
     * Ambil isi file: dari disk "public" lokal bila URL-nya /storage/..., selain itu via HTTP.
     *
     * @return array{0: string, 1: string} [isi, mime type]
     */
    protected function fetch(string $url): array
    {
        $path = rawurldecode((string) parse_url($url, PHP_URL_PATH));

        if (preg_match('~/storage/(.+)$~', $path, $m) && Storage::disk('public')->exists($m[1])) {
            /** @var \Illuminate\Filesystem\FilesystemAdapter $public */
            $public = Storage::disk('public');

            return [$public->get($m[1]), $public->mimeType($m[1]) ?: 'application/octet-stream'];
        }

        if (! parse_url($url, PHP_URL_HOST)) {
            $url = rtrim(config('app.url'), '/') . '/' . ltrim($url, '/');
        }

        // Coba ulang hanya untuk gangguan jaringan / error server; 4xx (akses ditolak, tidak ada) langsung gagal.
        $response = Http::timeout(60)
            ->retry(2, 1000, fn (Throwable $e) => $e instanceof ConnectionException
                || ($e instanceof RequestException && $e->response->serverError()))
            ->get($url);
        $mime = trim(explode(';', $response->header('Content-Type') ?: 'application/octet-stream')[0]);

        return [$response->body(), $mime];
    }

    /**
     * Tentukan path di S3. Struktur folder lama dipertahankan:
     *   Cloudinary  .../upload/v123/avatars/abc.jpg -> avatars/abc.jpg
     *   Lokal       /storage/letter-images/x.png    -> letter-images/x.png
     *   Lainnya                                     -> {folder}/{nama file}
     */
    protected function targetKey(string $url, string $folder): string
    {
        $path = rawurldecode((string) parse_url($url, PHP_URL_PATH));

        if (preg_match('~/upload/(?:.*?/)?v\d+/(.+)$~', $path, $m) || preg_match('~/storage/(.+)$~', $path, $m)) {
            $key = $m[1];
        } else {
            $key = trim($folder, '/') . '/' . (basename($path) ?: Str::random(40));
        }

        return trim(preg_replace('~[^A-Za-z0-9/_.-]+~', '-', $key), '/');
    }

    protected function sourceDisabled(Response $response): bool
    {
        return in_array($response->status(), [401, 403], true)
            && str_contains(strtolower($response->header('X-Cld-Error') . ' ' . $response->body()), 'is disabled');
    }

    protected function isOnTargetDisk(string $url): bool
    {
        return FileStorage::pathFromUrl($url) !== null;
    }
}

/** Menghentikan seluruh migrasi (mis. akun sumber dinonaktifkan). */
class MigrationAborted extends \RuntimeException
{
}
