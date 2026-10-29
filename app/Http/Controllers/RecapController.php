<?php

namespace App\Http\Controllers;

use App\Services\FirebaseSensorService;
use App\Services\FonnteService;
use App\Services\SensorMessageService;
use Illuminate\Http\Request;

class RecapController extends Controller
{
    public function send(Request $request, FirebaseSensorService $sensor, FonnteService $fonnte, SensorMessageService $message)
    {
        if ($request->query('secret') !== env('RECAP_SECRET')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $data = $sensor->realtime();
            if (empty($data)) {
                return response()->json(['error' => 'No sensor data found'], 404);
            }

            $response = $fonnte->send($message->recap($data));
            $response->throw();

            return response()->json(['success' => true, 'message' => 'Recap sent', 'wa' => $response->json()]);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }
}
