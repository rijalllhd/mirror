<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecapController;

// POST /api/recap?secret=YOUR_SECRET → kirim recap ke WhatsApp
Route::post('/recap', [RecapController::class, 'send']);

// GET /api/recap → info endpoint
Route::get('/recap', function () {
    return response()->json([
        'info'   => 'POST ke endpoint ini dengan ?secret=YOUR_RECAP_SECRET untuk kirim recap WhatsApp',
        'path'   => config('firebase_client.sensor_path'),
        'target' => 'Fonnte API → WhatsApp',
    ]);
});
