<div>
    <header class="mb-8">
        <div class="flex min-w-0 items-start gap-4">
            <x-ui.business-logo :business="$business" size="lg" class="rounded-2xl shadow-sm" />
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Portal de participantes</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $business->name }}</h1>
                @if($business->tagline)
                    <p class="mt-1 text-sm italic text-slate-600">{{ $business->tagline }}</p>
                @endif
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    {{ org_term('Datos del negocio', $business) }}. Usa el menú para abrir los módulos disponibles.
                </p>
            </div>
        </div>
    </header>

    <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">{{ org_term('Datos del negocio', $business) }}</h2>
        </div>
        <dl class="divide-y divide-slate-100 px-5 py-2">
            <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                <dt class="text-xs font-medium text-slate-500">Nombre</dt>
                <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->name ?: '—' }}</dd>
            </div>
            @if($business->organization_type)
            <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                <dt class="text-xs font-medium text-slate-500">Organización</dt>
                <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->organization_type->name }}</dd>
            </div>
            @endif
            @if($business->business_type)
            <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                <dt class="text-xs font-medium text-slate-500">Tipo</dt>
                <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->business_type->name }}</dd>
            </div>
            @endif
            @if($business->nit)
            <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                <dt class="text-xs font-medium text-slate-500">NIT</dt>
                <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->nit }}</dd>
            </div>
            @endif
            @if($business->address)
            <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                <dt class="text-xs font-medium text-slate-500">Dirección</dt>
                <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->address }}</dd>
            </div>
            @endif
            @if($business->phone_number)
            <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                <dt class="text-xs font-medium text-slate-500">Teléfono</dt>
                <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->phone_number }}</dd>
            </div>
            @endif
            @if($business->email)
            <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                <dt class="text-xs font-medium text-slate-500">Correo</dt>
                <dd class="text-sm text-slate-900 sm:col-span-2">{{ $business->email }}</dd>
            </div>
            @endif
            @if($business->website)
            <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                <dt class="text-xs font-medium text-slate-500">Sitio web</dt>
                <dd class="text-sm text-slate-900 sm:col-span-2">
                    <a href="{{ $business->website }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:text-indigo-700">{{ $business->website }}</a>
                </dd>
            </div>
            @endif
            @if($business->city || $business->country)
            <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                <dt class="text-xs font-medium text-slate-500">Ubicación</dt>
                <dd class="text-sm text-slate-900 sm:col-span-2">
                    {{ collect([$business->city?->name, $business->country?->name])->filter()->join(', ') ?: '—' }}
                </dd>
            </div>
            @endif
        </dl>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm ring-1 ring-slate-900/[0.035] sm:p-8">
        <h2 class="mb-4 font-semibold text-slate-800">Módulos disponibles</h2>
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
