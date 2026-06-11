<?php

namespace App\Console\Commands;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Illuminate\Console\Command;

class S3SetupCors extends Command
{
    protected $signature = 's3:setup-cors
                            {--origin=* : Дополнительный Origin (можно указать несколько раз)}
                            {--show : Только показать текущий CORS, не менять}';

    protected $description = 'Настраивает CORS на S3-бакете Beget Cloud, чтобы Filament FilePond мог показывать превью загруженных файлов';

    public function handle(): int
    {
        $cfg    = config('filesystems.disks.s3');
        $bucket = $cfg['bucket'] ?? null;

        if (!$bucket || empty($cfg['key']) || empty($cfg['secret'])) {
            $this->error('S3 не настроен в config/filesystems.disks.s3 (нет bucket / key / secret).');
            return self::FAILURE;
        }

        try {
            $client = new S3Client([
                'version'                 => 'latest',
                'region'                  => $cfg['region'] ?? 'us-east-1',
                'endpoint'                => $cfg['endpoint'] ?? null,
                'use_path_style_endpoint' => (bool) ($cfg['use_path_style_endpoint'] ?? true),
                'credentials'             => [
                    'key'    => $cfg['key'],
                    'secret' => $cfg['secret'],
                ],
            ]);
        } catch (\Throwable $e) {
            $this->error('Не удалось создать S3-клиент: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info("Бакет: {$bucket}");
        $this->info('Endpoint: ' . ($cfg['endpoint'] ?? '(default AWS)'));

        if ($this->option('show')) {
            try {
                $result = $client->getBucketCors(['Bucket' => $bucket]);
                $this->line('Текущая конфигурация CORS:');
                $this->line(json_encode($result->toArray()['CORSRules'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            } catch (S3Exception $e) {
                if ($e->getAwsErrorCode() === 'NoSuchCORSConfiguration') {
                    $this->warn('CORS не настроен — превью файлов в админке работать не будет.');
                } else {
                    $this->error('Ошибка получения CORS: ' . $e->getAwsErrorMessage());
                }
            }
            return self::SUCCESS;
        }

        $appUrl = rtrim(config('app.url', ''), '/');
        $origins = collect([
            $appUrl,
            'http://localhost',
        ])
            ->merge($this->option('origin'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->info('Разрешаем Origin:');
        foreach ($origins as $o) {
            $this->line('  • ' . $o);
        }

        $rules = [
            [
                'AllowedOrigins' => $origins,
                'AllowedMethods' => ['GET', 'HEAD'],
                'AllowedHeaders' => ['*'],
                'ExposeHeaders'  => ['ETag', 'Content-Length', 'Content-Type'],
                'MaxAgeSeconds'  => 3600,
            ],
        ];

        if (!$this->confirm('Применить эти правила к бакету?', true)) {
            $this->warn('Отменено.');
            return self::SUCCESS;
        }

        try {
            $client->putBucketCors([
                'Bucket'            => $bucket,
                'CORSConfiguration' => ['CORSRules' => $rules],
            ]);
            $this->info('✔ CORS применён.');
            $this->line('Проверь — в админке должны загружаться превью обложек.');
            return self::SUCCESS;
        } catch (S3Exception $e) {
            $this->error('Не удалось применить CORS: ' . $e->getAwsErrorMessage());
            $this->line('Code: ' . $e->getAwsErrorCode());
            return self::FAILURE;
        }
    }
}
