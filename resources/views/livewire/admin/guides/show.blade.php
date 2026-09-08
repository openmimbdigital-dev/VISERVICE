<div class="relative mx-auto w-full max-w-[90rem]">
    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-400">
        <a href="{{ route('dashboard') }}" wire:navigate class="transition hover:text-indigo-600">Inicio</a>
        <span>/</span>
        <a href="{{ route('admin.guides.index') }}" wire:navigate class="transition hover:text-indigo-600">Guías</a>
        <span>/</span>
        <span class="text-slate-600">{{ $guide->module }}</span>
    </nav>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_16rem]">
        <article class="order-2 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035] lg:order-1">
            <header class="border-b border-slate-100 bg-slate-50/80 px-6 py-5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $guide->type === 'onboarding' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/20' }}">
                        {{ $guide->typeLabel() }}
                    </span>
                    <span class="text-xs font-medium text-slate-500">{{ $guide->module }}</span>
                    @can('guides.manage')
                        @unless($guide->published)
                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700 ring-1 ring-amber-600/20">Borrador</span>
                        @endunless
                        @if($guide->visible_to_businesses)
                        <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-1 text-[11px] font-semibold text-sky-700 ring-1 ring-sky-600/20">Visible para negocios</span>
                        @endif
                    @endcan
                </div>
                <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">{{ $guide->title }}</h1>
                @if($guide->summary)
                <p class="mt-2 max-w-2xl text-sm text-slate-600">{{ $guide->summary }}</p>
                @endif
                <p class="mt-3 text-[11px] text-slate-400">
                    Actualizada {{ $guide->updated_at?->diffForHumans() }}
                    @if($guide->createdBy) · creada por {{ $guide->createdBy->full_name ?? $guide->createdBy->username }} @endif
                </p>
            </header>

            {{-- El Markdown ya viene con el HTML de entrada escapado en el modelo. --}}
            <div class="guide-content px-6 py-6">
                {!! $rendered !!}
            </div>
        </article>

        <aside class="order-1 lg:order-2">
            <div class="sticky top-4 space-y-4">
                <a href="{{ route('admin.guides.index') }}" wire:navigate class="btn btn-secondary btn-sm w-full justify-center">
                    Volver al listado
                </a>

                @if($headings !== [])
                <nav class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm">
                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">En esta guía</p>
                    <ul class="space-y-1.5">
                        @foreach($headings as $heading)
                        <li>
                            <a href="#{{ $heading['anchor'] }}" class="block text-xs text-slate-600 transition hover:text-indigo-600">
                                {{ $heading['title'] }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </nav>
                @endif

                @if($siblings->isNotEmpty())
                <nav class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm">
                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Del mismo módulo</p>
                    <ul class="space-y-1.5">
                        @foreach($siblings as $sibling)
                        <li>
                            <a href="{{ route('admin.guides.show', $sibling) }}" wire:navigate class="block text-xs text-slate-600 transition hover:text-indigo-600">
                                {{ $sibling->title }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </nav>
                @endif
            </div>
        </aside>
    </div>
</div>
