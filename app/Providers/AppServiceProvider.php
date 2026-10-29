<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Wasmer exposes the persistent writable filesystem at /data. XLSX
        // generation needs a temporary ZIP file, so do not depend on /app
        // being writable there.
        if ($temporaryPath = env('EXCEL_TEMPORARY_PATH')) {
            config()->set('excel.temporary_files.local_path', $temporaryPath);
        }

        $this->ensureFirebaseCredentialsExist();
    }

    public function boot(): void
    {
        $this->ensureFirebaseCredentialsExist();
    }

    private function ensureFirebaseCredentialsExist(): void
    {
        $targetPath = config('firebase_client.credentials', storage_path('app/firebase-credentials.json'));

        if (file_exists($targetPath)) {
            return;
        }

        $base64 = getenv('FIREBASE_CREDENTIALS_BASE64') ?: env('FIREBASE_CREDENTIALS_BASE64');
        if (! $base64) {
            return;
        }

        $json = base64_decode($base64, true);
        if ($json === false || json_decode($json, true) === null) {
            throw new \RuntimeException('FIREBASE_CREDENTIALS_BASE64 harus berisi JSON service-account Firebase yang valid dalam format Base64.');
        }

        $directory = dirname($targetPath);
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new \RuntimeException("Tidak dapat membuat direktori Firebase credentials: {$directory}");
        }

        if (file_put_contents($targetPath, $json, LOCK_EX) === false) {
            throw new \RuntimeException("Tidak dapat menulis Firebase credentials: {$targetPath}");
        }

        @chmod($targetPath, 0600);
    }
}
