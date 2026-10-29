<?php

namespace App\Services;

use DateTime;

/**
 * Builds the exact month/day structure rendered by the Riwayat view.
 * Exports use this too, so their numbers cannot drift from the screen.
 */
class HistoryViewDataService
{
    public function monthly(array $rawHistory): array
    {
        $monthly = [];
        $daily = [];

        foreach ($rawHistory as $entry) {
            if (! is_array($entry) || ! isset($entry['timestamp']) || $this->isAmoniaOutlier($entry)) continue;
            $time = DateTime::createFromFormat('Y-m-d H:i:s', (string) $entry['timestamp']);
            if (! $time) continue;

            $month = $time->format('Y-m');
            $date = $time->format('Y-m-d');
            $daily[$month][$date][] = $entry;
            $monthly[$month] ??= ['suhu' => [], 'kelembapan' => [], 'amonia_ppm' => [], 'thi' => []];

            foreach (array_keys($monthly[$month]) as $metric) {
                if (isset($entry[$metric])) $monthly[$month][$metric][] = $entry[$metric];
            }
        }

        krsort($monthly);
        $result = [];
        foreach ($monthly as $month => $metrics) {
            $record = [
                'yearMonth' => $month,
                'display' => DateTime::createFromFormat('Y-m', $month)?->format('F Y') ?? $month,
                'suhu' => $this->stats($metrics['suhu']),
                'kelembapan' => $this->stats($metrics['kelembapan']),
                'amonia_ppm' => $this->stats($metrics['amonia_ppm']),
                'thi' => $this->stats($metrics['thi']),
                'dataPoints' => count($metrics['suhu']),
                'days' => $this->days($daily[$month] ?? []),
            ];
            foreach (['suhu', 'kelembapan', 'amonia_ppm', 'thi'] as $metric) $record[$metric.'_avg'] = $record[$metric]['avg'];
            $result[] = $record;
        }

        return $result;
    }

    private function days(array $byDay): array
    {
        krsort($byDay);
        $result = [];
        foreach ($byDay as $date => $entries) {
            $values = ['suhu' => [], 'kelembapan' => [], 'amonia_ppm' => [], 'thi' => []];
            foreach ($entries as $entry) foreach (array_keys($values) as $metric) if (isset($entry[$metric])) $values[$metric][] = $entry[$metric];
            $record = [
                'date' => $date,
                'dateDisplay' => (new DateTime($date))->format('d M Y'),
                'suhu' => $this->stats($values['suhu']),
                'kelembapan' => $this->stats($values['kelembapan']),
                'amonia_ppm' => $this->stats($values['amonia_ppm']),
                'thi' => $this->stats($values['thi']),
                'dataPoints' => count($values['suhu']),
                'entries' => $this->entries($entries),
            ];
            foreach (array_keys($values) as $metric) $record[$metric.'_avg'] = $record[$metric]['avg'];
            $result[] = $record;
        }
        return $result;
    }

    private function entries(array $entries): array
    {
        usort($entries, fn ($a, $b) => strcmp($b['timestamp'] ?? '', $a['timestamp'] ?? ''));
        return array_map(fn ($entry) => [
            'timestamp' => $entry['timestamp'] ?? '-',
            'timeDisplay' => ($time = DateTime::createFromFormat('Y-m-d H:i:s', $entry['timestamp'] ?? '')) ? $time->format('H:i:s') : '-',
            'suhu' => $this->number($entry['suhu'] ?? null), 'kelembapan' => $this->number($entry['kelembapan'] ?? null),
            'amonia_ppm' => $this->number($entry['amonia_ppm'] ?? null), 'thi' => $this->number($entry['thi'] ?? null),
        ], $entries);
    }

    private function stats(array $values): array
    {
        $numbers = array_values(array_filter(array_map(fn ($value) => $this->number($value), $values), fn ($value) => $value !== null));
        return $numbers === [] ? ['min' => 0, 'avg' => 0, 'max' => 0] : ['min' => min($numbers), 'avg' => array_sum($numbers) / count($numbers), 'max' => max($numbers)];
    }

    private function number(mixed $value): ?float { return is_numeric($value) ? (float) $value : null; }
    private function isAmoniaOutlier(array $entry): bool { return isset($entry['amonia_ppm']) && is_numeric($entry['amonia_ppm']) && (float) $entry['amonia_ppm'] > config('sensor_alerts.history_max_amonia'); }
}
