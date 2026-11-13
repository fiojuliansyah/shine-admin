<?php

namespace App\Console\Commands;

use App\Services\FileStorage;
use Illuminate\Console\Command;
use Throwable;

/**
 * Sebagian provider S3-compatible (mis. Cloudeka) mengabaikan ACL public-read saat upload,
 * sehingga akses baca publik harus diatur lewat bucket policy.
 *
 * Policy ini hanya membuka folder FileStorage::PUBLIC_FOLDERS (avatar, foto wajah, logo,
 * gambar surat). Folder private (dokumen KTP/KK/SKCK, e-sign) tetap tertutup dan dibuka
 * lewat link sementara.
 */
class ApplyS3PublicPolicy extends Command
{
    protected $signature = 'media:s3-public-policy
        {--show : Tampilkan policy tanpa menerapkannya}
        {--remove : Hapus bucket policy (semua file kembali private)}';

    protected $description = 'Pasang bucket policy baca-publik untuk folder file publik (avatar, logo, gambar surat)';

    public function handle(): int
    {
        $disk = FileStorage::diskName();

        if (config("filesystems.disks.{$disk}.driver") !== 's3') {
            $this->error("UPLOAD_DISK saat ini \"{$disk}\", bukan disk S3.");

            return self::FAILURE;
        }

        $bucket = config("filesystems.disks.{$disk}.bucket");
        $client = FileStorage::disk()->getClient();
        $policy = json_encode([
            'Version' => '2012-10-17',
            'Statement' => [[
                'Sid' => 'PublicReadNonSensitiveFolders',
                'Effect' => 'Allow',
                'Principal' => ['AWS' => ['*']],
                'Action' => ['s3:GetObject'],
                'Resource' => array_map(fn ($folder) => "arn:aws:s3:::{$bucket}/{$folder}/*", FileStorage::PUBLIC_FOLDERS),
            ]],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($this->option('show')) {
            $this->line($policy);

            return self::SUCCESS;
        }

        try {
            if ($this->option('remove')) {
                $client->deleteBucketPolicy(['Bucket' => $bucket]);
                $this->info("Bucket policy {$bucket} dihapus.");

                return self::SUCCESS;
            }

            $client->putBucketPolicy(['Bucket' => $bucket, 'Policy' => $policy]);
        } catch (Throwable $e) {
            $this->error('Gagal: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info("Bucket policy {$bucket} terpasang. Folder publik: " . implode(', ', FileStorage::PUBLIC_FOLDERS));
        $this->line('Folder private (link sementara): ' . implode(', ', FileStorage::PRIVATE_FOLDERS));

        return self::SUCCESS;
    }
}
