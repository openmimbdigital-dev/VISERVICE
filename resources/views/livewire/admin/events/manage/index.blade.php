<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.events.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Gestión de eventos</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.events.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Eventos</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Administrar</span>
    </nav>

    <header class="mb-8 space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Gestión de eventos</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Administrar eventos</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    Selecciona una categoría para ver y gestionar sus eventos.
                </p>
            </div>
            @if($can_view_schedule)
                <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                    <a href="{{ route('admin.events.schedule.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                        Ver agenda
                    </a>
                </div>
            @endif
        </div>
        <div class="grid w-full grid-cols-2 gap-3 sm:max-w-md">
            <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Categorías</p>
                <p class="mt-2 text-2xl font-semibold tabular-nums text-slate-900 sm:text-3xl">{{ $stats['categories'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Eventos</p>
                <p class="mt-2 text-2xl font-semibold tabular-nums text-indigo-600 sm:text-3xl">{{ $stats['events'] }}</p>
            </div>
        </div>
    </header>

    @php
        $card_styles = [
            ['card_bg' => 'bg-violet-50/60 border-violet-100/80', 'icon_bg' => 'bg-violet-100', 'icon_c' => 'text-violet-600'],
            ['card_bg' => 'bg-indigo-50/60 border-indigo-100/80', 'icon_bg' => 'bg-indigo-100', 'icon_c' => 'text-indigo-600'],
            ['card_bg' => 'bg-sky-50/60 border-sky-100/80', 'icon_bg' => 'bg-sky-100', 'icon_c' => 'text-sky-600'],
            ['card_bg' => 'bg-emerald-50/60 border-emerald-100/80', 'icon_bg' => 'bg-emerald-100', 'icon_c' => 'text-emerald-600'],
            ['card_bg' => 'bg-amber-50/60 border-amber-100/80', 'icon_bg' => 'bg-amber-100', 'icon_c' => 'text-amber-600'],
            ['card_bg' => 'bg-rose-50/60 border-rose-100/80', 'icon_bg' => 'bg-rose-100', 'icon_c' => 'text-rose-600'],
        ];
    @endphp

    @if($categories->isNotEmpty())
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @foreach($categories as $category)
                @php $style = $card_styles[$loop->index % count($card_styles)]; @endphp
                <article class="group flex min-h-[220px] flex-col rounded-2xl border p-5 shadow-sm ring-1 ring-slate-900/[0.035] transition-all hover:shadow-md {{ $style['card_bg'] }}">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $style['icon_bg'] }} ring-1 ring-black/[0.04] transition-transform group-hover:scale-105">
                            <svg class="h-5 w-5 {{ $style['icon_c'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-semibold text-slate-900">{{ $category->name }}</h2>
                                <span class="inline-flex items-center rounded-full bg-white/80 px-2 py-0.5 text-[10px] font-medium text-slate-600 ring-1 ring-slate-500/15">
                                    {{ $category->type?->label() ?? '—' }}
                                </span>
                                @if($category->general)
                                    <span class="inline-flex items-center rounded-full bg-white/80 px-2 py-0.5 text-[10px] font-medium text-indigo-600 ring-1 ring-indigo-500/20">
                                        General
                                    </span>
                                @endif
                            </div>
                            <p class="mt-1.5 text-sm text-slate-600">
                                {{ $category->events_count }} evento(s)
                            </p>
                            @if($category->description)
                                <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ $category->description }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-auto flex flex-col gap-2 pt-6 sm:flex-row">
                        <a href="{{ route('admin.events.manage.category.index', $category) }}" wire:navigate
                            class="btn btn-primary btn-sm w-full justify-center sm:flex-1">
                            <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            Ver eventos
                        </a>
                        @if($can_create)
                            <a href="{{ route('admin.events.manage.category.create', $category) }}" wire:navigate
                                class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">
                                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Nuevo
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-8 text-center shadow-sm ring-1 ring-slate-900/[0.035]">
            <p class="text-sm text-slate-600">No hay categorías de eventos disponibles.</p>
        </section>
    @endif
</div>
