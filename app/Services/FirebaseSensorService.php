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
