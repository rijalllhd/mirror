<?php

namespace App\Services;

class SensorMessageService
{
    public function recap(array $data): string
    {
        $thi = (float) ($data['THI'] ?? 0);
        $amonia = (float) ($data['Amonia_PPM'] ?? 0);
        $kondisi = $this->kandangStatus($data);

        return "*Rekap Sensor SKARP*\n"
            . now('Asia/Jakarta')->translatedFormat('l, d F Y H:i').' WIB' . "\n\n"
            . '*Kondisi kandang:* '.$kondisi."\n\n"
            . '*Suhu:* '.number_format((float) ($data['Suhu'] ?? 0), 1)." C\n"
            . '*Kelembapan:* '.number_format((float) ($data['Kelembapan'] ?? 0), 1)." %\n"
            . '*THI:* '.number_format($thi, 2).' - '.$this->thiStatus($thi)."\n"
            . '*Amonia:* '.number_format($amonia, 5).' ppm - '.$this->amoniaStatus($amonia);
    }

    public function highAlert(array $data): string
    {
        $thi = (float) ($data['THI'] ?? 0);
        $amonia = (float) ($data['Amonia_PPM'] ?? 0);
        $suhu = (float) ($data['Suhu'] ?? 0);
        $kelembapan = (float) ($data['Kelembapan'] ?? 0);
        $triggers = [];

        if ($amonia > config('sensor_alerts.extreme_amonia')) {
            $triggers[] = 'Amonia di atas '.number_format(config('sensor_alerts.extreme_amonia'), 0).' ppm';
        }

        if ($thi > config('sensor_alerts.extreme_thi')) {
            $triggers[] = 'THI di atas '.number_format(config('sensor_alerts.extreme_thi'), 0);
        }

        return "*PERINGATAN SENSOR SKARP*\n"
            . now('Asia/Jakarta')->format('d-m-Y H:i:s').' WIB' . "\n\n"
            . '*Kondisi ekstrem:* '.implode(' / ', $triggers)."\n\n"
            . '*Suhu:* '.number_format($suhu, 1)." C\n"
            . '*Kelembapan:* '.number_format($kelembapan, 1)." %\n"
            . '*THI:* '.number_format($thi, 2)."\n"
            . '*Amonia:* '.number_format($amonia, 5)." ppm\n\n"
            . 'Segera periksa kondisi kandang dan perangkat.';
    }

    private function thiStatus(float $thi): string
    {
        return $thi >= config('sensor_alerts.thi') ? 'Bahaya' : ($thi >= 72 ? 'Waspada' : 'Nyaman');
    }

    private function amoniaStatus(float $amonia): string
    {
        return $amonia >= config('sensor_alerts.amonia') ? 'Bahaya' : ($amonia >= 20 ? 'Waspada' : 'Aman');
    }

    private function kandangStatus(array $data): string
    {
        $values = [
            'suhu' => $data['Suhu'] ?? null,
            'kelembapan' => $data['Kelembapan'] ?? null,
            'amonia' => $data['Amonia_PPM'] ?? null,
            'thi' => $data['THI'] ?? null,
        ];

        if (collect($values)->contains(fn ($value) => ! is_numeric($value))) {
            return 'Data sensor tidak lengkap';
        }

        $danger = (float) $values['suhu'] >= 35
            || (float) $values['kelembapan'] >= 80
            || (float) $values['amonia'] >= config('sensor_alerts.amonia')
            || (float) $values['thi'] >= config('sensor_alerts.thi');
        if ($danger) {
            return 'Bahaya';
        }

        $warning = (float) $values['suhu'] >= 30
            || (float) $values['kelembapan'] >= 70
            || (float) $values['amonia'] >= 20
            || (float) $values['thi'] >= 72;

        return $warning ? 'Waspada' : 'Aman';
    }
}
