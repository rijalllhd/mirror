<?php

namespace App\Services;

use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Factory;

class FirebaseSensorService
{
    public function realtime(): array
    {
        return $this->database()->getReference($this->sensorPath().'/Realtime')->getValue() ?? [];
    }

    public function history(): array
    {
        return $this->database()->getReference($this->sensorPath().'/History')->getValue() ?? [];
    }

    public function writeReading(array $reading, string $id): void
    {
        $history = [
            'suhu' => $reading['Suhu'],
            'kelembapan' => $reading['Kelembapan'],
            'amonia_ppm' => $reading['Amonia_PPM'],
            'thi' => $reading['THI'],
            'timestamp' => $reading['timestamp'],
        ];

        $this->database()->getReference()->update([
            $this->sensorPath().'/Realtime' => $reading,
            $this->sensorPath().'/History/'.$id => $history,
        ]);
    }

    private function database(): Database
    {
        $credentialsPath = config('firebase_client.credentials');

        if (! file_exists($credentialsPath)) {
            throw new \RuntimeException("Firebase credentials file not found at: {$credentialsPath}");
        }

        return (new Factory())
            ->withServiceAccount($credentialsPath)
            ->withDatabaseUri(config('firebase_client.database_url'))
            ->createDatabase();
    }

    private function sensorPath(): string
    {
        return config('firebase_client.sensor_path');
    }
}
