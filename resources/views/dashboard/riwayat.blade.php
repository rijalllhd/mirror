@extends('layouts.app')

@section('title', 'Riwayat - SKARP IoT')

@section('content')
<div class="min-h-screen bg-slate-100 p-4 sm:p-6 xl:p-8 2xl:p-10">
    <div class="mx-auto flex w-full max-w-[1800px] flex-col gap-4 lg:flex-row xl:gap-6">
        @include('dashboard.partials.sidebar')

        <main class="min-w-0 flex-1">
            <section class="rounded-lg border border-white/70 bg-white/80 p-5 shadow-sm sm:p-6">
                <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-widest text-sky-700">History Sensor</p>
                        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">Riwayat Sensor</h1>
                        <p class="mt-2 max-w-2xl text-sm font-medium text-slate-500">Rekap histori sensor per bulan dengan rincian harian yang bisa dibuka.</p>
                    </div>
                    <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-extrabold text-slate-500">{{ count($monthlyHistory) }} bulan</span>
                </div>

                @if (session('exportError'))
                    <div role="alert" class="mb-5 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">
                        {{ session('exportError') }}
                    </div>
                @endif

                <form class="mb-5 flex flex-col gap-3 border-b border-slate-200 pb-5 lg:flex-row lg:flex-wrap lg:items-end" method="GET">
                    <div class="w-full sm:w-56">
                        <label for="period" class="mb-1 block text-xs font-bold text-slate-600">Periode ekspor</label>
                        <select id="period" name="period" class="w-full rounded-md border-slate-300 text-sm focus:border-sky-600 focus:ring-sky-600">
                            <option value="all">Semua data</option>
                            <option value="last_month">1 bulan terakhir</option>
                            <option value="range">Rentang tanggal</option>
                        </select>
                    </div>
                    <div id="range-fields" class="hidden flex flex-col gap-3 sm:flex-row">
                        <div><label for="start_date" class="mb-1 block text-xs font-bold text-slate-600">Tanggal awal</label><input id="start_date" type="date" name="start_date" class="rounded-md border-slate-300 text-sm focus:border-sky-600 focus:ring-sky-600"></div>
                        <div><label for="end_date" class="mb-1 block text-xs font-bold text-slate-600">Tanggal akhir</label><input id="end_date" type="date" name="end_date" class="rounded-md border-slate-300 text-sm focus:border-sky-600 focus:ring-sky-600"></div>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row lg:ml-auto">
                        <button type="submit" formaction="{{ route('riwayat.export.excel') }}" class="inline-flex w-full items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800 sm:w-auto">Export Excel</button>
                        <button type="submit" formaction="{{ route('riwayat.export.pdf') }}" class="inline-flex w-full items-center justify-center rounded-md bg-rose-700 px-4 py-2 text-sm font-bold text-white hover:bg-rose-800 sm:w-auto">PDF Ringkasan</button>
                    </div>
                </form>

                @include('dashboard.partials.history-table')
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
function setToggleState(toggle, isExpanded, activeColor) {
    if (!toggle) return;
    toggle.textContent = isExpanded ? '-' : '+';
    toggle.dataset.expanded = isExpanded ? 'true' : 'false';
    toggle.style.backgroundColor = isExpanded ? activeColor : '#ffffff';
    toggle.style.borderColor = isExpanded ? activeColor : '#e2e8f0';
    toggle.style.color = isExpanded ? '#ffffff' : activeColor;
}

document.querySelectorAll('.month-row').forEach((monthRow) => {
    const toggle = monthRow.querySelector('.month-toggle');
    const yearMonth = monthRow.dataset.month;
    const dayRows = document.querySelectorAll(`.day-row-${yearMonth}`);
    setToggleState(toggle, false, '#0369a1');

    monthRow.addEventListener('click', () => {
        const isExpanded = toggle?.dataset.expanded !== 'true';
        dayRows.forEach((row) => row.classList.toggle('hidden', !isExpanded));
        if (!isExpanded) {
            dayRows.forEach((row) => {
                const dayToggle = row.querySelector('.day-toggle');
                document.querySelectorAll(`.entry-row-${row.dataset.day}`).forEach((entryRow) => entryRow.classList.add('hidden'));
                setToggleState(dayToggle, false, '#0f766e');
            });
        }
        setToggleState(toggle, isExpanded, '#0369a1');
    });
});

document.querySelectorAll('.day-row').forEach((dayRow) => {
    const toggle = dayRow.querySelector('.day-toggle');
    const dayKey = dayRow.dataset.day;
    const entryRows = document.querySelectorAll(`.entry-row-${dayKey}`);
    setToggleState(toggle, false, '#0f766e');

    dayRow.addEventListener('click', (event) => {
        event.stopPropagation();
        const isExpanded = toggle?.dataset.expanded !== 'true';
        entryRows.forEach((row) => row.classList.toggle('hidden', !isExpanded));
        setToggleState(toggle, isExpanded, '#0f766e');
    });
});

const periodSelect = document.getElementById('period');
const rangeFields = document.getElementById('range-fields');
const startDate = document.getElementById('start_date');
const endDate = document.getElementById('end_date');
function updateRangeFields() {
    const isRange = periodSelect.value === 'range';
    rangeFields.classList.toggle('hidden', !isRange);
    startDate.required = isRange;
    endDate.required = isRange;
}
periodSelect?.addEventListener('change', updateRangeFields);
updateRangeFields();
</script>
@endpush
