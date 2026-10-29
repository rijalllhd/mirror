<?php

namespace App\Http\Controllers;

use App\Services\HistoryViewDataService;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class DashboardController extends Controller
{
    public function index(HistoryViewDataService $historyView)
    {
        return view('dashboard.index', $this->dashboardData($historyView));
    }

    public function analitic(HistoryViewDataService $historyView)
    {
        return view('dashboard.analitic', $this->dashboardData($historyView));
    }

    public function riwayat(HistoryViewDataService $historyView)
    {
        return view('dashboard.riwayat', $this->dashboardData($historyView));
    }

    private function dashboardData(HistoryViewDataService $historyView)
    {
        $firebaseConfig = [
            'apiKey'            => config('firebase_client.api_key'),
            'authDomain'        => config('firebase_client.auth_domain'),
            'databaseURL'       => config('firebase_client.database_url'),
            'projectId'         => config('firebase_client.project_id'),
            'storageBucket'     => config('firebase_client.storage_bucket'),
            'messagingSenderId' => config('firebase_client.messaging_sender_id'),
            'appId'             => config('firebase_client.app_id'),
        ];

        $sensorPath = config('firebase_client.sensor_path');
        
        // Fetch data from Firebase Admin SDK
        $realtimeData = [];
        $monthlyHistory = [];
        $historyChartData = [
            'labels' => [],
            'suhu' => [],
            'kelembapan' => [],
            'amonia_ppm' => [],
            'thi' => [],
        ];
        
        try {
            $credentialsPath = config('firebase_client.credentials');
            
            // Check if credentials file exists
            if (!file_exists($credentialsPath)) {
                throw new \Exception("Firebase credentials file not found at: $credentialsPath");
            }
            
            $factory = (new Factory)->withServiceAccount($credentialsPath)
                                    ->withDatabaseUri(config('firebase_client.database_url'));
            $database = $factory->createDatabase();
            
            // Get Realtime data
            $realtimeSnapshot = $database->getReference($sensorPath . '/Realtime')->getSnapshot();
            if ($realtimeSnapshot->exists()) {
                $realtimeData = $realtimeSnapshot->getValue() ?? [];
            }
            
            // Get History data and aggregate by month
            $historySnapshot = $database->getReference($sensorPath . '/History')->getSnapshot();
            if ($historySnapshot->exists()) {
                $rawHistory = $historySnapshot->getValue() ?? [];
                $monthlyHistory = $historyView->monthly($rawHistory);
                $historyChartData = $this->buildHistoryChartData($monthlyHistory);
            }
        } catch (\Exception $e) {
            // Log error but don't crash - show empty history
            \Log::error('Firebase data fetch error: ' . $e->getMessage());
            $monthlyHistory = [];
        }

        return compact('firebaseConfig', 'sensorPath', 'realtimeData', 'monthlyHistory', 'historyChartData');
    }

    /**
     * Aggregate history data by month with daily breakdown
     */
    private function aggregateByMonth($rawHistory)
    {
        $monthlyData = [];
        $dailyData = [];

        foreach ($rawHistory as $entry) {
            if (!isset($entry['timestamp'])) continue;
            if ($this->isAmoniaOutlier($entry)) continue;

            $timestamp = $entry['timestamp'];
            $dateObj = \DateTime::createFromFormat('Y-m-d H:i:s', $timestamp);
            if (!$dateObj) continue;

            $yearMonth = $dateObj->format('Y-m');
            $day = $dateObj->format('Y-m-d');

            // Group by day first
            if (!isset($dailyData[$yearMonth])) {
                $dailyData[$yearMonth] = [];
            }
            if (!isset($dailyData[$yearMonth][$day])) {
                $dailyData[$yearMonth][$day] = [];
            }

            $dailyData[$yearMonth][$day][] = $entry;

            // Also track for monthly aggregation
            if (!isset($monthlyData[$yearMonth])) {
                $monthlyData[$yearMonth] = [
                    'suhu' => [],
                    'kelembapan' => [],
                    'amonia_ppm' => [],
                    'thi' => [],
                ];
            }

            if (isset($entry['suhu'])) $monthlyData[$yearMonth]['suhu'][] = $entry['suhu'];
            if (isset($entry['kelembapan'])) $monthlyData[$yearMonth]['kelembapan'][] = $entry['kelembapan'];
            if (isset($entry['amonia_ppm'])) $monthlyData[$yearMonth]['amonia_ppm'][] = $entry['amonia_ppm'];
            if (isset($entry['thi'])) $monthlyData[$yearMonth]['thi'][] = $entry['thi'];
        }

        // Calculate averages and build final structure
        $result = [];
        krsort($monthlyData); // Latest months first

        foreach ($monthlyData as $yearMonth => $metrics) {
            $monthRecord = [
                'yearMonth' => $yearMonth,
                'display' => $this->formatMonthDisplay($yearMonth),
                'suhu' => $this->metricStats($metrics['suhu']),
                'kelembapan' => $this->metricStats($metrics['kelembapan']),
                'amonia_ppm' => $this->metricStats($metrics['amonia_ppm']),
                'thi' => $this->metricStats($metrics['thi']),
                'dataPoints' => count($metrics['suhu']),
                'days' => $this->formatDayDetails($dailyData[$yearMonth] ?? []),
            ];

            $monthRecord['suhu_avg'] = $monthRecord['suhu']['avg'];
            $monthRecord['kelembapan_avg'] = $monthRecord['kelembapan']['avg'];
            $monthRecord['amonia_ppm_avg'] = $monthRecord['amonia_ppm']['avg'];
            $monthRecord['thi_avg'] = $monthRecord['thi']['avg'];

            $result[] = $monthRecord;
        }

        return $result;
    }

    private function metricStats(array $values)
    {
        $numbers = array_values(array_filter(array_map(function ($value) {
            return is_numeric($value) ? (float) $value : null;
        }, $values), fn ($value) => $value !== null));

        if (count($numbers) === 0) {
            return ['min' => 0, 'avg' => 0, 'max' => 0];
        }

        return [
            'min' => min($numbers),
            'avg' => array_sum($numbers) / count($numbers),
            'max' => max($numbers),
        ];
    }

    /**
     * Format month for display
     */
    private function formatMonthDisplay($yearMonth)
    {
        $date = \DateTime::createFromFormat('Y-m', $yearMonth);
        return $date ? $date->format('F Y') : $yearMonth;
    }

    /**
     * Format daily details for month
     */
    private function formatDayDetails($dailyData)
    {
        $result = [];
        krsort($dailyData); // Latest days first

        foreach ($dailyData as $day => $entries) {
            if (empty($entries)) continue;

            $dayAvg = [
                'date' => $day,
                'dateDisplay' => (new \DateTime($day))->format('d M Y'),
                'suhu' => [],
                'kelembapan' => [],
                'amonia_ppm' => [],
                'thi' => [],
            ];

            foreach ($entries as $entry) {
                if (isset($entry['suhu'])) $dayAvg['suhu'][] = $entry['suhu'];
                if (isset($entry['kelembapan'])) $dayAvg['kelembapan'][] = $entry['kelembapan'];
                if (isset($entry['amonia_ppm'])) $dayAvg['amonia_ppm'][] = $entry['amonia_ppm'];
                if (isset($entry['thi'])) $dayAvg['thi'][] = $entry['thi'];
            }

            $dayRecord = [
                'date' => $day,
                'dateDisplay' => $dayAvg['dateDisplay'],
                'suhu' => $this->metricStats($dayAvg['suhu']),
                'kelembapan' => $this->metricStats($dayAvg['kelembapan']),
                'amonia_ppm' => $this->metricStats($dayAvg['amonia_ppm']),
                'thi' => $this->metricStats($dayAvg['thi']),
                'dataPoints' => count($dayAvg['suhu']),
                'entries' => $this->formatHistoryEntries($entries),
            ];

            $dayRecord['suhu_avg'] = $dayRecord['suhu']['avg'];
            $dayRecord['kelembapan_avg'] = $dayRecord['kelembapan']['avg'];
            $dayRecord['amonia_ppm_avg'] = $dayRecord['amonia_ppm']['avg'];
            $dayRecord['thi_avg'] = $dayRecord['thi']['avg'];

            $result[] = $dayRecord;
        }

        return $result;
    }

    private function formatHistoryEntries(array $entries)
    {
        usort($entries, function ($a, $b) {
            return strcmp($b['timestamp'] ?? '', $a['timestamp'] ?? '');
        });

        return array_map(function ($entry) {
            return [
                'timestamp' => $entry['timestamp'] ?? '-',
                'timeDisplay' => $this->formatTimeDisplay($entry['timestamp'] ?? null),
                'suhu' => $this->numericValue($entry['suhu'] ?? null),
                'kelembapan' => $this->numericValue($entry['kelembapan'] ?? null),
                'amonia_ppm' => $this->numericValue($entry['amonia_ppm'] ?? null),
                'thi' => $this->numericValue($entry['thi'] ?? null),
            ];
        }, $entries);
    }

    private function numericValue($value)
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function isAmoniaOutlier(array $entry): bool
    {
        return isset($entry['amonia_ppm'])
            && is_numeric($entry['amonia_ppm'])
            && (float) $entry['amonia_ppm'] > config('sensor_alerts.history_max_amonia');
    }

    private function formatTimeDisplay($timestamp)
    {
        if (!$timestamp) return '-';

        $dateObj = \DateTime::createFromFormat('Y-m-d H:i:s', $timestamp);
        return $dateObj ? $dateObj->format('H:i:s') : $timestamp;
    }

    private function buildHistoryChartData(array $monthlyHistory)
    {
        $days = [];

        foreach ($monthlyHistory as $month) {
            foreach ($month['days'] as $day) {
                $days[] = $day;
            }
        }

        usort($days, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        return [
            'labels' => array_map(fn ($day) => $day['dateDisplay'], $days),
            'suhu' => array_map(fn ($day) => round($day['suhu']['avg'], 2), $days),
            'kelembapan' => array_map(fn ($day) => round($day['kelembapan']['avg'], 2), $days),
            'amonia_ppm' => array_map(fn ($day) => round($day['amonia_ppm']['avg'], 5), $days),
            'thi' => array_map(fn ($day) => round($day['thi']['avg'], 2), $days),
        ];
    }
}
