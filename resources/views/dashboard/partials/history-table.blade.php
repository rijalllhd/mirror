@php
    $formatMetric = fn ($value, $digits = 1) => is_numeric($value) ? number_format($value, $digits) : '-';
@endphp

@if(count($monthlyHistory) > 0)
    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
        <table class="min-w-[1380px] w-full border-collapse text-sm">
            <thead>
                <tr class="bg-slate-50 text-left text-xs font-extrabold uppercase tracking-wide text-slate-500">
                    <th rowspan="2" class="w-12 px-4 py-3"></th>
                    <th rowspan="2" class="px-4 py-3">Periode</th>
                    <th colspan="3" class="border-l border-slate-200 px-4 py-3 text-center">Suhu (&deg;C)</th>
                    <th colspan="3" class="border-l border-slate-200 px-4 py-3 text-center">RH (%)</th>
                    <th colspan="3" class="border-l border-slate-200 px-4 py-3 text-center">Amonia (ppm)</th>
                    <th colspan="3" class="border-l border-slate-200 px-4 py-3 text-center">THI</th>
                    <th rowspan="2" class="border-l border-slate-200 px-4 py-3 text-center">Data</th>
                </tr>
                <tr class="bg-slate-50 text-xs font-extrabold uppercase tracking-wide text-slate-500">
                    <th class="border-l border-slate-200 px-3 py-2 text-center">Terendah</th>
                    <th class="px-3 py-2 text-center">Rata-rata</th>
                    <th class="px-3 py-2 text-center">Tertinggi</th>
                    <th class="border-l border-slate-200 px-3 py-2 text-center">Terendah</th>
                    <th class="px-3 py-2 text-center">Rata-rata</th>
                    <th class="px-3 py-2 text-center">Tertinggi</th>
                    <th class="border-l border-slate-200 px-3 py-2 text-center">Terendah</th>
                    <th class="px-3 py-2 text-center">Rata-rata</th>
                    <th class="px-3 py-2 text-center">Tertinggi</th>
                    <th class="border-l border-slate-200 px-3 py-2 text-center">Terendah</th>
                    <th class="px-3 py-2 text-center">Rata-rata</th>
                    <th class="px-3 py-2 text-center">Tertinggi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($monthlyHistory as $month)
                    <tr class="month-row cursor-pointer bg-white font-bold text-slate-800 hover:bg-slate-50" data-month="{{ $month['yearMonth'] }}">
                        <td class="px-4 py-3">
                            <span class="month-toggle inline-flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-sky-700">+</span>
                        </td>
                        <td class="px-4 py-3">{{ $month['display'] }}</td>
                        <td class="border-l border-slate-100 px-3 py-3 text-center text-sky-700">{{ $formatMetric($month['suhu']['min'], 1) }}</td>
                        <td class="px-3 py-3 text-center text-sky-700">{{ $formatMetric($month['suhu']['avg'], 1) }}</td>
                        <td class="px-3 py-3 text-center text-sky-700">{{ $formatMetric($month['suhu']['max'], 1) }}</td>
                        <td class="border-l border-slate-100 px-3 py-3 text-center text-teal-700">{{ $formatMetric($month['kelembapan']['min'], 1) }}</td>
                        <td class="px-3 py-3 text-center text-teal-700">{{ $formatMetric($month['kelembapan']['avg'], 1) }}</td>
                        <td class="px-3 py-3 text-center text-teal-700">{{ $formatMetric($month['kelembapan']['max'], 1) }}</td>
                        <td class="border-l border-slate-100 px-3 py-3 text-center text-amber-700">{{ $formatMetric($month['amonia_ppm']['min'], 3) }}</td>
                        <td class="px-3 py-3 text-center text-amber-700">{{ $formatMetric($month['amonia_ppm']['avg'], 3) }}</td>
                        <td class="px-3 py-3 text-center text-amber-700">{{ $formatMetric($month['amonia_ppm']['max'], 3) }}</td>
                        <td class="border-l border-slate-100 px-3 py-3 text-center text-violet-700">{{ $formatMetric($month['thi']['min'], 2) }}</td>
                        <td class="px-3 py-3 text-center text-violet-700">{{ $formatMetric($month['thi']['avg'], 2) }}</td>
                        <td class="px-3 py-3 text-center text-violet-700">{{ $formatMetric($month['thi']['max'], 2) }}</td>
                        <td class="border-l border-slate-100 px-4 py-3 text-center text-slate-500">{{ $month['dataPoints'] }}</td>
                    </tr>

                    @foreach($month['days'] as $day)
                        @php
                            $dayKey = $month['yearMonth'] . '-' . str_replace('-', '', $day['date']);
                        @endphp
                        <tr class="day-row day-row-{{ $month['yearMonth'] }} hidden cursor-pointer bg-slate-50 text-sm font-semibold text-slate-600 hover:bg-slate-100" data-day="{{ $dayKey }}">
                            <td class="px-4 py-3">
                                <span class="day-toggle inline-flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-teal-700">+</span>
                            </td>
                            <td class="px-4 py-3">{{ $day['dateDisplay'] }}</td>
                            <td class="border-l border-slate-200 px-3 py-3 text-center text-sky-700">{{ $formatMetric($day['suhu']['min'], 1) }}</td>
                            <td class="px-3 py-3 text-center text-sky-700">{{ $formatMetric($day['suhu']['avg'], 1) }}</td>
                            <td class="px-3 py-3 text-center text-sky-700">{{ $formatMetric($day['suhu']['max'], 1) }}</td>
                            <td class="border-l border-slate-200 px-3 py-3 text-center text-teal-700">{{ $formatMetric($day['kelembapan']['min'], 1) }}</td>
                            <td class="px-3 py-3 text-center text-teal-700">{{ $formatMetric($day['kelembapan']['avg'], 1) }}</td>
                            <td class="px-3 py-3 text-center text-teal-700">{{ $formatMetric($day['kelembapan']['max'], 1) }}</td>
                            <td class="border-l border-slate-200 px-3 py-3 text-center text-amber-700">{{ $formatMetric($day['amonia_ppm']['min'], 3) }}</td>
                            <td class="px-3 py-3 text-center text-amber-700">{{ $formatMetric($day['amonia_ppm']['avg'], 3) }}</td>
                            <td class="px-3 py-3 text-center text-amber-700">{{ $formatMetric($day['amonia_ppm']['max'], 3) }}</td>
                            <td class="border-l border-slate-200 px-3 py-3 text-center text-violet-700">{{ $formatMetric($day['thi']['min'], 2) }}</td>
                            <td class="px-3 py-3 text-center text-violet-700">{{ $formatMetric($day['thi']['avg'], 2) }}</td>
                            <td class="px-3 py-3 text-center text-violet-700">{{ $formatMetric($day['thi']['max'], 2) }}</td>
                            <td class="border-l border-slate-200 px-4 py-3 text-center text-slate-500">{{ $day['dataPoints'] }}</td>
                        </tr>

                        @foreach($day['entries'] as $entry)
                            <tr class="entry-row entry-row-{{ $dayKey }} hidden bg-white text-xs font-semibold text-slate-500">
                                <td class="px-4 py-3"></td>
                                <td class="px-4 py-3">
                                    <span class="rounded bg-slate-100 px-2 py-1 text-slate-700">{{ $entry['timeDisplay'] }}</span>
                                </td>
                                <td class="border-l border-slate-100 px-3 py-3 text-center">-</td>
                                <td class="px-3 py-3 text-center text-sky-700">{{ $formatMetric($entry['suhu'], 1) }}</td>
                                <td class="px-3 py-3 text-center">-</td>
                                <td class="border-l border-slate-100 px-3 py-3 text-center">-</td>
                                <td class="px-3 py-3 text-center text-teal-700">{{ $formatMetric($entry['kelembapan'], 1) }}</td>
                                <td class="px-3 py-3 text-center">-</td>
                                <td class="border-l border-slate-100 px-3 py-3 text-center">-</td>
                                <td class="px-3 py-3 text-center text-amber-700">{{ $formatMetric($entry['amonia_ppm'], 3) }}</td>
                                <td class="px-3 py-3 text-center">-</td>
                                <td class="border-l border-slate-100 px-3 py-3 text-center">-</td>
                                <td class="px-3 py-3 text-center text-violet-700">{{ $formatMetric($entry['thi'], 2) }}</td>
                                <td class="px-3 py-3 text-center">-</td>
                                <td class="border-l border-slate-100 px-4 py-3 text-center text-slate-400">1</td>
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-sm font-semibold text-slate-500">
        Data histori bulanan belum tersedia. Pastikan credentials Firebase Admin ada di <code class="rounded bg-slate-100 px-1 py-0.5 text-slate-700">storage/app/firebase-credentials.json</code>.
    </div>
@endif
