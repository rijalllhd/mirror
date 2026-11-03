<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardReadingService
{
    private const CACHE_KEY = 'dashboard.current-reading.v2';
    private const LOCK_KEY = 'dashboard.current-reading.lock';
    private const NEXT_INSERT_KEY = 'dashboard.next-firebase-insert-at';

    public function current(): array
    {
        return Cache::lock(self::LOCK_KEY, 10)->block(3, function (): array {
            $now = microtime(true);
            $state = Cache::get(self::CACHE_KEY);

            $requiredState = ['thi', 'amonia', 'humidity', 'period', 'minimum_thi', 'maximum_thi', 'updated_at'];
            if (! is_array($state) || array_diff($requiredState, array_keys($state)) !== []) {
                $state = $this->initialState($now);
            }

            $steps = min(3600, max(0, (int) floor(($now - $state['updated_at']) / 10)));
            for ($i = 0; $i < $steps; $i++) {
                $state['updated_at'] += 10;
                $nowForStep = Carbon::createFromTimestamp((int) $state['updated_at'], 'Asia/Jakarta');
                $range = $this->thiRange($nowForStep);
                if ($range['period'] !== $state['period']) {
                    $state['period'] = $range['period'];
                    $state['minimum_thi'] = $range['minimum'];
                    $state['maximum_thi'] = $range['maximum'];
                }
                $state['thi'] = $this->moveWithinRange($state['thi'], $state['minimum_thi'], $state['maximum_thi']);
                $state['amonia'] = max(5, min(13, $state['amonia'] + $this->randomStep()));
                $state['humidity'] = max(60, min(80, $state['humidity'] + random_int(-2, 2) / 10));
            }

            $reading = $this->toReading($state, Carbon::createFromTimestamp((int) $state['updated_at'], 'Asia/Jakarta'));
            Cache::put(self::CACHE_KEY, $state, now()->addDays(2));

            return $reading;
        });
    }

    public function isInsertDue(): bool
    {
        $nextInsertAt = Cache::get(self::NEXT_INSERT_KEY);

        return ! is_numeric($nextInsertAt) || now()->timestamp >= (int) $nextInsertAt;
    }

    public function scheduleNextInsert(): Carbon
    {
        $nextInsertAt = now()->addMinutes(random_int(29, 36));
        Cache::put(self::NEXT_INSERT_KEY, $nextInsertAt->timestamp, now()->addDays(2));

        return $nextInsertAt;
    }

    private function initialState(float $now): array
    {
        $range = $this->thiRange(Carbon::now('Asia/Jakarta'));

        return [
            'thi' => random_int((int) ($range['minimum'] * 10), (int) ($range['maximum'] * 10)) / 10,
            'amonia' => random_int(50, 130) / 10,
            'humidity' => random_int(650, 740) / 10,
            'period' => $range['period'],
            'minimum_thi' => $range['minimum'],
            'maximum_thi' => $range['maximum'],
            'updated_at' => $now,
        ];
    }

    private function thiRange(Carbon $now): array
    {
        if ($now->hour >= 12 && $now->hour < 16) {
            return ['period' => $now->format('Y-m-d').'-midday', 'minimum' => 81, 'maximum' => 83];
        }

        if ($now->hour >= 6 && $now->hour < 18) {
            $period = $now->hour < 12 ? 'morning' : 'afternoon';
            return ['period' => $now->format('Y-m-d').'-'.$period, 'minimum' => 80, 'maximum' => 81];
        }

        $range = random_int(0, 9) === 0 ? [80, 81] : [76, 79];
        return ['period' => $now->format('Y-m-d-H').'-night', 'minimum' => $range[0], 'maximum' => $range[1]];
    }

    private function randomStep(): float
    {
        return random_int(0, 1) === 0 ? -random_int(1, 3) / 10 : random_int(1, 3) / 10;
    }

    private function moveWithinRange(float $current, float $minimum, float $maximum): float
    {
        $step = random_int(1, 3) / 10;
        if ($current < $minimum) {
            return min($minimum, $current + $step);
        }
        if ($current > $maximum) {
            return max($maximum, $current - $step);
        }

        return max($minimum, min($maximum, $current + $this->randomStep()));
    }

    private function toReading(array $state, Carbon $now): array
    {
        $thi = round($state['thi'], 2);
        $humidity = round($state['humidity'], 1);
        $amonia = round($state['amonia'], 1);
        $temperature = ($thi - 32 - 26 * (0.55 - 0.0055 * $humidity))
            / (1.8 * (1 - 0.55 + 0.0055 * $humidity));

        return [
            'Suhu' => round($temperature, 1),
            'Kelembapan' => $humidity,
            'THI' => $thi,
            'Amonia_PPM' => $amonia,
            'Amonia_Raw' => '-',
            'Status_Amonia' => 'Normal',
            'Status_Kipas' => $thi > 79 ? 'MENYALA' : 'MATI',
            'Status_Nozzle' => $amonia > 20 ? 'MENYALA' : 'MATI',
            'timestamp' => $now->format('Y-m-d H:i:s'),
        ];
    }
}
