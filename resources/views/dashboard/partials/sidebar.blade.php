@php
    $items = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'grid'],
        ['route' => 'analitic', 'label' => 'Analitic', 'icon' => 'chart'],
        ['route' => 'riwayat', 'label' => 'Riwayat', 'icon' => 'history'],
    ];
@endphp

<aside class="flex shrink-0 flex-col gap-6 rounded-lg border border-white/70 bg-white/80 p-4 shadow-sm lg:sticky lg:top-6 lg:h-[calc(100vh-3rem)] lg:w-64 xl:top-8 xl:h-[calc(100vh-4rem)]">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-2">
        <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-white shadow-sm">
            <img src="{{ asset('images/logoskarprill.png') }}" alt="SKARP Logo" class="h-8 w-8 object-contain">
        </span>
        <span>
            <span class="block text-base font-extrabold text-slate-950">SKARP</span>
            <span class="block text-xs font-semibold text-slate-500">IoT Dashboard</span>
        </span>
    </a>

    <nav class="grid gap-2">
        <p class="px-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">Menu</p>
        @foreach($items as $item)
            @php
                $active = request()->routeIs($item['route']);
            @endphp
            <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-bold transition {{ $active ? 'bg-sky-700 text-white shadow-lg shadow-sky-700/20' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-950' }}">
                @if($item['icon'] === 'grid')
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                @elseif($item['icon'] === 'chart')
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19V5"/><path d="M4 19h16"/><path d="M8 16V9"/><path d="M12 16V6"/><path d="M16 16v-4"/></svg>
                @else
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><path d="M8 2v4"/><path d="M16 2v4"/><path d="M4 10h16"/></svg>
                @endif
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="mt-auto rounded-lg bg-slate-950 p-4 text-white">
        <p class="text-sm font-extrabold">Kualitas Kandang</p>
        <p class="mt-2 text-xs leading-5 text-slate-300">Monitoring suhu, RH, THI, amonia, kipas, dan nozzle.</p>
        <p class="mt-4 text-[11px] font-bold uppercase tracking-widest text-sky-200">Data Dari Firebase Realtime</p>
    </div>
</aside>
