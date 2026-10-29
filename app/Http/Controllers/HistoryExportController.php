<?php

namespace App\Http\Controllers;

use App\Exports\SensorHistoryExport;
use App\Services\FirebaseSensorService;
use App\Services\HistoryViewDataService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class HistoryExportController extends Controller
{
    public function excel(Request $request, FirebaseSensorService $sensor, HistoryViewDataService $historyView)
    {
        // Pengambilan data sama dengan ekspor PDF. Jika bagian ini gagal,
        // kemungkinan penyebabnya memang Firebase/parameter filter.
        $filter = $this->filter($request, $sensor, $historyView);

        try {
            return Excel::download(
                new SensorHistoryExport($filter['rows']),
                'riwayat-sensor-'.$filter['suffix'].'.xlsx',
                \Maatwebsite\Excel\Excel::XLSX,
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            );
        } catch (\Throwable $exception) {
            report($exception);
            Log::warning('XLSX export gagal; mengirim CSV yang kompatibel dengan Excel sebagai fallback.', [
                'exception' => $exception->getMessage(),
            ]);

            return $this->csvDownload($filter['rows'], $filter['suffix']);
        }
    }

    public function pdf(Request $request, FirebaseSensorService $sensor, HistoryViewDataService $historyView)
    {
        $filter = $this->filter($request, $sensor, $historyView);
        return Pdf::loadView('dashboard.exports.history-pdf', $filter)
            ->setPaper('a4', 'landscape')
            ->download('riwayat-sensor-'.$filter['suffix'].'.pdf');
    }

    private function filter(Request $request, FirebaseSensorService $sensor, HistoryViewDataService $historyView): array
    {
        $validated = $request->validate([
            'period' => ['nullable', 'in:all,last_month,range'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
        $period = $validated['period'] ?? 'all';

        if ($period === 'range' && (! $request->filled('start_date') || ! $request->filled('end_date'))) {
            abort(422, 'Tanggal awal dan akhir wajib diisi untuk pilihan rentang.');
        }

        $start = $period === 'last_month' ? now('Asia/Jakarta')->subMonth()->startOfDay() : ($request->filled('start_date') ? Carbon::parse($request->start_date, 'Asia/Jakarta')->startOfDay() : null);
        $end = $request->filled('end_date') ? Carbon::parse($request->end_date, 'Asia/Jakarta')->endOfDay() : null;

        // Use the same month/day presentation model as dashboard Riwayat,
        // rather than exporting unprocessed Firebase readings.
        $rows = collect($historyView->monthly($sensor->history()))
            ->flatMap(fn (array $month) => $month['days'])
            ->filter(function (array $day) use ($start, $end) {
                $timestamp = Carbon::parse($day['date'], 'Asia/Jakarta')->startOfDay();
                return (! $start || $timestamp->gte($start)) && (! $end || $timestamp->lte($end));
            })
            ->sortBy('date')
            ->values()->all();

        $labels = ['all' => 'Semua data', 'last_month' => '1 bulan terakhir', 'range' => 'Rentang '.$request->start_date.' s.d. '.$request->end_date];
        return ['rows' => $rows, 'periodLabel' => $labels[$period], 'suffix' => now()->format('Ymd-His')];
    }

    private function csvDownload(array $rows, string $suffix)
    {
        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Tanggal', 'Data', 'Suhu Min', 'Suhu Rata-rata', 'Suhu Maks', 'Kelembapan Min', 'Kelembapan Rata-rata', 'Kelembapan Maks', 'Amonia Min', 'Amonia Rata-rata', 'Amonia Maks', 'THI Min', 'THI Rata-rata', 'THI Maks'], ';');

            foreach ($rows as $row) {
                fputcsv($output, [
                    $row['date'], $row['dataPoints'],
                    $row['suhu']['min'], $row['suhu']['avg'], $row['suhu']['max'],
                    $row['kelembapan']['min'], $row['kelembapan']['avg'], $row['kelembapan']['max'],
                    $row['amonia_ppm']['min'], $row['amonia_ppm']['avg'], $row['amonia_ppm']['max'],
                    $row['thi']['min'], $row['thi']['avg'], $row['thi']['max'],
                ], ';');
            }

            fclose($output);
        }, 'riwayat-sensor-'.$suffix.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
