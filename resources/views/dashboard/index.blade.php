@extends('layouts.app')

@section('title', 'Dashboard - SKARP IoT')

@section('content')
<div class="min-h-screen bg-slate-100 p-4 sm:p-6 xl:p-8 2xl:p-10">
    <div class="mx-auto flex w-full max-w-[1800px] flex-col gap-4 lg:flex-row xl:gap-6">
        @include('dashboard.partials.sidebar')

        <main class="min-w-0 flex-1">
            <section class="rounded-lg border border-white/70 bg-white/80 p-5 shadow-sm sm:p-6">
                <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-widest text-sky-700">IoT Monitoring</p>
                        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950">Dashboard Sensor</h1>
                        <p class="mt-2 max-w-2xl text-sm font-medium text-slate-500">Pantau kondisi kandang dan status aktuator secara realtime.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <div id="conn-badge" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-extrabold text-slate-500">
                            <span id="conn-dot" class="h-2 w-2 rounded-full bg-current"></span>
                            <span id="conn-text">Menghubungkan...</span>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 2xl:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="grid gap-4 sm:grid-cols-2 2xl:grid-cols-4">
                        <article class="rounded-lg bg-sky-700 p-5 text-white shadow-lg shadow-sky-700/20">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/15">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"/></svg>
                                    </div>
                                    <p class="mt-4 text-sm font-extrabold text-sky-100">Suhu</p>
                                </div>
                                <div id="badge-suhu" class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-extrabold text-white">
                                    <span class="h-2 w-2 rounded-full bg-current"></span><span id="label-suhu">-</span>
                                </div>
                            </div>
                            <div class="mt-5 flex items-baseline gap-2">
                                <span id="val-suhu" class="text-4xl font-extrabold leading-none">-</span>
                                <span class="text-sm font-bold text-sky-100">&deg;C</span>
                            </div>
                        </article>

                        <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-50 text-teal-700">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11z"/></svg>
                                    </div>
                                    <p class="mt-4 text-sm font-extrabold text-slate-500">Kelembapan</p>
                                </div>
                                <div id="badge-kelembapan" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-extrabold text-slate-500">
                                    <span class="h-2 w-2 rounded-full bg-current"></span><span id="label-kelembapan">-</span>
                                </div>
                            </div>
                            <div class="mt-5 flex items-baseline gap-2">
                                <span id="val-kelembapan" class="text-4xl font-extrabold leading-none text-slate-950">-</span>
                                <span class="text-sm font-bold text-slate-500">%</span>
                            </div>
                        </article>

                        <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 14a8 8 0 0 1 16 0"/><path d="M6 14h12"/><path d="M9 18h6"/></svg>
                                    </div>
                                    <p class="mt-4 text-sm font-extrabold text-slate-500">Amonia</p>
                                </div>
                                <div id="badge-amonia" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-extrabold text-slate-500">
                                    <span class="h-2 w-2 rounded-full bg-current"></span><span id="label-amonia">-</span>
                                </div>
                            </div>
                            <div class="mt-5 flex items-baseline gap-2">
                                <span id="val-amonia" class="text-4xl font-extrabold leading-none text-slate-950">-</span>
                                <span class="text-sm font-bold text-slate-500">ppm</span>
                            </div>
                        </article>

                        <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-50 text-violet-700">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19V5"/><path d="M4 19h16"/><path d="M8 16V9"/><path d="M12 16V6"/><path d="M16 16v-4"/></svg>
                                    </div>
                                    <p class="mt-4 text-sm font-extrabold text-slate-500">THI</p>
                                </div>
                                <div id="badge-thi" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-extrabold text-slate-500">
                                    <span class="h-2 w-2 rounded-full bg-current"></span><span id="label-thi">-</span>
                                </div>
                            </div>
                            <div class="mt-5">
                                <span id="val-thi" class="text-4xl font-extrabold leading-none text-slate-950">-</span>
                            </div>
                        </article>
                    </div>

                    <aside class="grid gap-4 sm:grid-cols-2 2xl:grid-cols-1">
                        <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-extrabold text-slate-500">Kipas</p>
                                    <p id="val-kipas" class="mt-4 text-3xl font-extrabold text-slate-950">-</p>
                                </div>
                                <!-- <div id="badge-kipas" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-extrabold text-slate-500">
                                    <span class="h-2 w-2 rounded-full bg-current"></span><span id="label-kipas">-</span>
                                </div> -->
                            </div>
                        </article>

                        <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-extrabold text-slate-500">Nozzle</p>
                                    <p id="val-nozzle" class="mt-4 text-3xl font-extrabold text-slate-950">-</p>
                                </div>
                                <!-- <div id="badge-nozzle" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-extrabold text-slate-500">
                                    <span class="h-2 w-2 rounded-full bg-current"></span><span id="label-nozzle">-</span>
                                </div> -->
                            </div>
                        </article>

                        <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-sm font-extrabold text-slate-950">Ringkasan</p>
                            <dl class="mt-4 grid gap-3 text-sm">
                                <div class="flex justify-between gap-4">
                                    <dt class="font-bold text-slate-500">Firebase Path</dt>
                                    <dd class="font-extrabold text-slate-800">{{ $sensorPath }}/Realtime</dd>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <dt class="font-bold text-slate-500">Histori Bulan</dt>
                                    <dd class="font-extrabold text-slate-800">{{ count($monthlyHistory) }}</dd>
                                </div>
                            </dl>
                        </article>
                    </aside>
                </div>

                <div id="raw-strip" class="mt-4 hidden flex-col gap-2 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm font-bold text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                    <span>Raw Values</span>
                    <span>Amonia Raw: <strong id="val-raw" class="text-slate-950">-</strong></span>
                    <span>Status Amonia: <strong id="val-status-amonia" class="text-slate-950">-</strong></span>
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
@include('dashboard.partials.realtime-script')
@endpush
