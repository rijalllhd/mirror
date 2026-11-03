<?php

namespace App\Console\Commands;

use App\Services\FirebaseSensorService;
use App\Services\DashboardReadingService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class GenerateSensorReading extends Command
{
    protected $signature = 'sensor:generate-reading {--scheduled : Insert only when the randomized interval is due}';
    protected $description = 'Simpan pembacaan yang sedang tampil di dashboard ke Firebase';

    public function handle(DashboardReadingService $readings, FirebaseSensorService $firebase): int
    {
        try {
            if ($this->option('scheduled') && ! $readings->isInsertDue()) {
                $this->line('Belum waktunya insert berikutnya.');
                return self::SUCCESS;
            }

            $now = Carbon::now('Asia/Jakarta');
            $reading = $readings->current();
            $reading['timestamp'] = $now->format('Y-m-d H:i:s');

            $firebase->writeReading($reading, (string) Str::uuid());
            $nextInsertAt = $readings->scheduleNextInsert();
            $this->info(sprintf(
                'Pembacaan dashboard tersimpan (%s, THI %s, amonia %s ppm). Insert berikutnya dijadwalkan setelah jeda acak, paling cepat %s WIB.',
                $reading['timestamp'], $reading['THI'], $reading['Amonia_PPM'], $nextInsertAt->timezone('Asia/Jakarta')->format('H:i')
            ));
            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $message = str_contains($exception->getMessage(), 'OpenSSL unable to validate key')
                ? 'Private key pada FIREBASE_CREDENTIALS tidak valid. Ganti dengan service-account JSON baru dari proyek Firebase yang benar.'
                : $exception->getMessage();

            $this->error('Gagal menyimpan pembacaan: '.$message);
            return self::FAILURE;
        }
    }
}
