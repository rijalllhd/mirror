<?php

namespace App\Console\Commands;

use App\Services\FirebaseSensorService;
use App\Services\FonnteService;
use App\Services\SensorMessageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendHighSensorAlert extends Command
{
    protected $signature = 'sensor:check-alerts';
    protected $description = 'Kirim peringatan WhatsApp ketika THI atau amonia sangat tinggi';

    public function handle(FirebaseSensorService $sensor, FonnteService $fonnte, SensorMessageService $message): int
    {
        try {
            // Sesudah pesan terkirim, jangan membaca Firebase lagi sampai masa tunggu selesai.
            if (Cache::has('sensor-high-alert-active')) {
                return self::SUCCESS;
            }

            $data = $sensor->realtime();
            $isDangerous = (float) ($data['THI'] ?? 0) > config('sensor_alerts.extreme_thi')
                || (float) ($data['Amonia_PPM'] ?? 0) > config('sensor_alerts.extreme_amonia');

            if (! $isDangerous) {
                return self::SUCCESS;
            }

            if (! config('services.fonnte.token') || ! config('services.fonnte.target')) {
                throw new \RuntimeException('FONNTE_TOKEN atau WA_TARGET_NUMBER belum dikonfigurasi.');
            }

            // The cache is written only after a confirmed send. A temporary API
            // failure therefore retries on the next scheduled run instead of
            // suppressing alerts for the whole cooldown period.
            if (Cache::has('sensor-high-alert-active')) {
                return self::SUCCESS;
            }

            $fonnte->send($message->highAlert($data));
            Cache::put('sensor-high-alert-active', true, now()->addMinutes(config('sensor_alerts.cooldown_minutes')));
            $this->warn('Peringatan WhatsApp terkirim.');
            return self::SUCCESS;
        } catch (\Throwable $exception) {
            report($exception);
            $this->error($exception->getMessage());
            return self::FAILURE;
        }
    }
}
