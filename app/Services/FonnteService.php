<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class FonnteService
{
    public function send(string $message): Response
    {
        $response = Http::timeout(20)
            ->retry(3, 500, throw: false)
            ->withHeaders(['Authorization' => config('services.fonnte.token')])
            ->asForm()
            ->post('https://api.fonnte.com/send', [
                'target' => config('services.fonnte.target'),
                'message' => $message,
            ]);

        $response->throw();

        // Fonnte can report a rejected message in a successful HTTP response.
        if (filter_var($response->json('status'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === false) {
            throw new \RuntimeException($response->json('reason') ?: $response->json('detail') ?: 'Fonnte menolak pengiriman pesan.');
        }

        return $response;
    }
}
