@extends('layouts.app')

@section('title', 'Analitic - SKARP IoT')

@section('content')
@php
    $latestIndex = count($historyChartData['labels']) - 1;
    $latestValue = function ($key, $digits = 1) use ($historyChartData, $latestIndex) {
        if ($latestIndex < 0 || !isset($historyChartData[$key][$latestIndex])) return '-';
        return number_format($historyChartData[$key][$latestIndex], $digits);
    };
@endphp
<div class="min-h-screen bg-slate-100 p-4 sm:p-6 xl:p-8 2xl:p-10">
    <div class="mx-auto flex w-full max-w-[1800px] flex-col gap-4 lg:flex-row xl:gap-6">
        @include('dashboard.partials.sidebar')

        <main class="min-w-0 flex-1">
            <section class="rounded-lg border border-white/70 bg-white/80 p-5 shadow-sm sm:p-6">
                <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-widest text-sky-700">History Analitic</p>
                        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">Analitic Sensor</h1>
                        <p class="mt-2 max-w-2xl text-sm font-medium text-slate-500">Sumbu X memakai tanggal history, sedangkan sumbu Y memakai nilai rata-rata variabel utama per hari.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">{{ count($historyChartData['labels']) }} hari history</span>
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-extrabold text-slate-950">Suhu</p>
                                <p class="text-xs font-bold text-slate-500">Derajat Celsius</p>
                            </div>
                            <span class="rounded-full border border-sky-100 bg-sky-50 px-3 py-1 text-xs font-extrabold text-sky-700">Y: suhu rata-rata</span>
                        </div>
                        <div class="h-64 2xl:h-80"><canvas id="chart-suhu"></canvas></div>
                        <p class="mt-4 text-2xl font-extrabold text-slate-950">{{ $latestValue('suhu', 1) }} <span class="text-sm text-slate-500">&deg;C terakhir</span></p>
                    </article>

                    <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-extrabold text-slate-950">Kelembapan</p>
                                <p class="text-xs font-bold text-slate-500">Relative Humidity</p>
                            </div>
                            <span class="rounded-full border border-teal-100 bg-teal-50 px-3 py-1 text-xs font-extrabold text-teal-700">Y: RH rata-rata</span>
                        </div>
                        <div class="h-64 2xl:h-80"><canvas id="chart-kelembapan"></canvas></div>
                        <p class="mt-4 text-2xl font-extrabold text-slate-950">{{ $latestValue('kelembapan', 1) }} <span class="text-sm text-slate-500">% terakhir</span></p>
                    </article>

                    <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-extrabold text-slate-950">Amonia</p>
                                <p class="text-xs font-bold text-slate-500">Parts per million</p>
                            </div>
                            <span class="rounded-full border border-amber-100 bg-amber-50 px-3 py-1 text-xs font-extrabold text-amber-700">Y: amonia rata-rata</span>
                        </div>
                        <div class="h-64 2xl:h-80"><canvas id="chart-amonia"></canvas></div>
                        <p class="mt-4 text-2xl font-extrabold text-slate-950">{{ $latestValue('amonia_ppm', 3) }} <span class="text-sm text-slate-500">ppm terakhir</span></p>
                    </article>

                    <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-extrabold text-slate-950">THI</p>
                                <p class="text-xs font-bold text-slate-500">Temperature Humidity Index</p>
                            </div>
                            <span class="rounded-full border border-violet-100 bg-violet-50 px-3 py-1 text-xs font-extrabold text-violet-700">Y: THI rata-rata</span>
                        </div>
                        <div class="h-64 2xl:h-80"><canvas id="chart-thi"></canvas></div>
                        <p class="mt-4 text-2xl font-extrabold text-slate-950">{{ $latestValue('thi', 2) }} <span class="text-sm text-slate-500">terakhir</span></p>
                    </article>
                </div>
            </section>

            <footer class="mt-4 flex flex-wrap justify-between gap-2 px-1 text-xs font-bold text-slate-500">
                <span>SKARP IoT Dashboard - Firebase Realtime DB</span>
                <span>{{ date('Y') }} - Bogor, Indonesia</span>
            </footer>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script>
const HISTORY_CHART_DATA = @json($historyChartData);

function makeHistoryChart(id, key, color, label, yTitle) {
    const canvas = document.getElementById(id);
    if (!canvas || typeof Chart === 'undefined') return;

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: HISTORY_CHART_DATA.labels,
            datasets: [{
                label,
                data: HISTORY_CHART_DATA[key],
                borderColor: color,
                backgroundColor: color + '22',
                borderWidth: 2,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 5,
                tension: 0.32,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#0f172a', padding: 10 },
            },
            scales: {
                x: {
                    title: { display: true, text: 'Tanggal history', color: '#64748b', font: { weight: 'bold' } },
                    grid: { display: false },
                    ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 6 },
                },
                y: {
                    title: { display: true, text: yTitle, color: '#64748b', font: { weight: 'bold' } },
                    grid: { color: 'rgba(148, 163, 184, 0.18)' },
                },
            },
        },
    });
}

makeHistoryChart('chart-suhu', 'suhu', '#0369a1', 'Suhu rata-rata', 'Suhu rata-rata');
makeHistoryChart('chart-kelembapan', 'kelembapan', '#0f766e', 'RH rata-rata', 'RH rata-rata');
makeHistoryChart('chart-amonia', 'amonia_ppm', '#b45309', 'Amonia rata-rata', 'Amonia rata-rata');
makeHistoryChart('chart-thi', 'thi', '#7c3aed', 'THI rata-rata', 'THI rata-rata');
</script>
@endpush
