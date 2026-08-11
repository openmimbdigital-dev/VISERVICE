<div>
    <header class="mb-8 border-l-4 border-indigo-600 pl-4 sm:pl-5">
        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Participantes</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Bienvenido</h1>
        <p class="mt-2 max-w-xl text-sm text-slate-600">
            Portal público de <strong>{{ $business_name }}</strong>. Usa el menú para abrir los módulos disponibles.
        </p>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm ring-1 ring-slate-900/[0.035] sm:p-8">
        @if($portal_items === [])
            <p class="text-sm text-slate-500">Por ahora no hay módulos habilitados para este negocio.</p>
        @else
            <ul class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach($portal_items as $item)
                    @if(! empty($item['url']))
                        <li>
                            <a href="{{ $item['url'] }}"
                                wire:navigate
                                class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm font-semibold text-slate-800 transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-800">
                                <span>{{ $item['label'] }}</span>
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        @endif
    </section>
</div>
