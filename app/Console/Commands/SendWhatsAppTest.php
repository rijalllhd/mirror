<?php

namespace App\Console\Commands;

use App\Services\FonnteService;
use Illuminate\Console\Command;

class SendWhatsAppTest extends Command
{
    protected $signature = 'whatsapp:test {message? : Pesan yang akan dikirim}';
    protected $description = 'Kirim pesan uji ke nomor WhatsApp yang dikonfigurasi';

    public function handle(FonnteService $fonnte): int
    {
        if (! config('services.fonnte.token') || ! config('services.fonnte.target')) {
            $this->error('FONNTE_TOKEN atau WA_TARGET_NUMBER belum diatur.');
            return self::FAILURE;
        }

        $message = $this->argument('message') ?: 'Tes WhatsApp SKARP berhasil pada '.now('Asia/Jakarta')->format('d-m-Y H:i:s').' WIB.';

        try {
            $response = $fonnte->send($message);
            $response->throw();
            $this->info('Pesan uji berhasil dikirim ke '.config('services.fonnte.target').'.');
            return self::SUCCESS;
        } catch (\Throwable $exception) {
            report($exception);
            $this->error('Pesan uji gagal: '.$exception->getMessage());
            return self::FAILURE;
        }
    }
}
