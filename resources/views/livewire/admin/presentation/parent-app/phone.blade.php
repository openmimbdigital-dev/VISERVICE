<div class="relative w-[320px] shrink-0 rounded-[2.7rem] bg-zinc-900 p-[10px] shadow-2xl shadow-slate-900/40 ring-1 ring-zinc-700">
    <div class="pointer-events-none absolute left-1/2 top-[12px] z-20 h-[22px] w-[108px] -translate-x-1/2 rounded-full bg-black"></div>
    <div class="relative flex h-[640px] flex-col overflow-hidden rounded-[2.15rem] bg-slate-50">
        <div class="flex shrink-0 items-center justify-between px-6 pb-1 pt-3 text-[11px] font-semibold text-slate-900">
            <span>9:41</span>
            <span class="flex items-center gap-1 text-slate-700">
                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path d="M2 8h2v8H2zm4-2h2v10H6zm4-2h2v12h-2zm4 3h2v9h-2zm4-3h2v12h-2z"/></svg>
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M4 8h13a2 2 0 012 2v6h1v2H2v-2h1v-6a2 2 0 012-2z"/></svg>
            </span>
        </div>

        <div class="flex shrink-0 items-center gap-2 border-b border-slate-200/80 bg-white px-4 py-2.5">
            @if($show_back)
            <button type="button" wire:click="go('{{ $tab }}')" class="rounded-lg p-1 text-indigo-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            @endif
            <div class="min-w-0 flex-1">
                <p class="truncate text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Colegio demo</p>
                <p class="truncate text-sm font-semibold text-slate-900">{{ $header_title }}</p>
            </div>
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-[11px] font-bold text-indigo-700">CM</div>
        </div>

        @if($flash !== '')
        <div class="mx-3 mt-2 rounded-xl bg-emerald-50 px-3 py-2 text-center text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20">{{ $flash }}</div>
        @endif

        <div class="min-h-0 flex-1 overflow-y-auto px-3 py-3">
            @include('livewire.admin.presentation.parent-app.screens')
        </div>

        <nav class="grid shrink-0 grid-cols-5 border-t border-slate-200 bg-white px-1 pb-3 pt-1.5">
            @foreach([
                ['key' => 'home', 'label' => 'Inicio', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['key' => 'classroom', 'label' => 'Aula', 'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z'],
                ['key' => 'courses', 'label' => 'Cursos', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                ['key' => 'payments', 'label' => 'Pagos', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                ['key' => 'events', 'label' => 'Eventos', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ] as $item)
            <button type="button" wire:click="go('{{ $item['key'] }}')"
                class="flex flex-col items-center gap-0.5 rounded-lg py-1 {{ $tab === $item['key'] ? 'text-indigo-600' : 'text-slate-400' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/></svg>
                <span class="text-[9px] font-semibold">{{ $item['label'] }}</span>
            </button>
            @endforeach
        </nav>
    </div>
</div>
