<div class="space-y-8">

    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 px-8 py-10 shadow-xl">
        <div class="absolute inset-0 opacity-30" style="background-image:radial-gradient(rgba(255,255,255,.15) 1px,transparent 1px);background-size:24px 24px;"></div>
        <div class="absolute -top-20 -right-20 h-64 w-64 rounded-full bg-indigo-600/20 blur-3xl"></div>
        <div class="absolute -bottom-16 -left-10 h-48 w-48 rounded-full bg-primary-700/20 blur-3xl"></div>

        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center gap-6">
            <img src="{{ asset('images/logo-initial.png') }}" alt="VISERVICE" class="h-16 w-auto drop-shadow-lg shrink-0">
            <div>
                <p class="text-indigo-300 text-xs font-semibold uppercase tracking-widest mb-1">Panel de control</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-white leading-tight">
                    Bienvenido, <span class="text-indigo-300">{{ auth()->user()?->full_name ?: auth()->user()?->username }}</span>
                </h1>
                <p class="mt-1.5 text-slate-400 text-sm">
                    Hoy es {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}.
                    Todo bajo control.
                </p>
            </div>
        </div>
    </div>

    {{-- Módulos --}}
    @php
        $modules = [
            [
                'title'   => 'Taller',
                'desc'    => 'Clientes, vehículos, cotizaciones y órdenes de trabajo.',
                'bg'      => 'bg-emerald-50 border-emerald-100',
                'icon_bg' => 'bg-emerald-100',
                'icon_c'  => 'text-emerald-600',
                'links'   => [
                    ['label' => 'Clientes',      'route' => 'admin.workshop.clients.index', 'permission' => 'workshop.clients.view'],
                    ['label' => 'Equipos',     'route' => 'admin.workshop.equipment.index', 'permission' => 'workshop.equipment.view'],
                    ['label' => 'Cotizaciones',  'route' => 'admin.workshop.quotations.index'],
                    ['label' => 'Órdenes (OT)',  'route' => 'admin.workshop.work-orders.index'],
                ],
                'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
            ],
            [
                'title'   => 'Catálogo',
                'desc'    => 'Servicios y repuestos para cotizaciones y OTs.',
                'bg'      => 'bg-amber-50 border-amber-100',
                'icon_bg' => 'bg-amber-100',
                'icon_c'  => 'text-amber-600',
                'links'   => [
                    ['label' => 'Servicios', 'route' => 'admin.workshop.catalog.services.index'],
                    ['label' => 'Repuestos', 'route' => 'admin.workshop.catalog.spare-parts.index'],
                ],
                'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
            ],
            [
                'title'   => 'Usuarios',
                'desc'    => 'Cuentas de usuario y asignación de roles.',
                'bg'      => 'bg-indigo-50 border-indigo-100',
                'icon_bg' => 'bg-indigo-100',
                'icon_c'  => 'text-indigo-600',
                'links'   => [
                    ['label' => 'Ver usuarios', 'route' => 'admin.users.index', 'permission' => 'users.view'],
                    ['label' => 'Roles',        'route' => 'admin.roles.index', 'permission' => 'roles.view'],
                ],
                'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
            ],
        ];
    @endphp

    @role('superAdmin')
    @php
        $modules[] = [
            'title'   => 'Suscripciones',
            'desc'    => 'Planes y facturación por comercio.',
            'bg'      => 'bg-violet-50 border-violet-100',
            'icon_bg' => 'bg-violet-100',
            'icon_c'  => 'text-violet-600',
            'links'   => [
                ['label' => 'Suscripciones', 'route' => 'admin.subscriptions.index'],
                ['label' => 'Planes',        'route' => 'admin.subscriptions.plans.index'],
            ],
            'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        ];
    @endphp
    @endrole

    <div>
        <h2 class="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-4">Módulos del sistema</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach($modules as $mod)
            <div class="rounded-2xl border {{ $mod['bg'] }} p-5 flex flex-col gap-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $mod['icon_bg'] }}">
                        <svg class="h-5 w-5 {{ $mod['icon_c'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $mod['icon'] }}"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900 text-sm">{{ $mod['title'] }}</h3>
                        <p class="text-xs text-slate-500 leading-snug mt-0.5">{{ $mod['desc'] }}</p>
                    </div>
                </div>
                <div class="flex flex-col gap-1.5">
                    @foreach($mod['links'] as $link)
                    @if(empty($link['permission']) || auth()->user()->can($link['permission']))
                    <a href="{{ route($link['route']) }}" wire:navigate
                        class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-200/80 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 transition-all group">
                        <svg class="h-3.5 w-3.5 text-slate-400 group-hover:text-slate-600 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        {{ $link['label'] }}
                    </a>
                    @endif
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Estado del sistema --}}
    <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></div>
            <span class="text-sm text-slate-600">Sistema operativo y en línea</span>
        </div>
        <span class="text-xs text-slate-400">VISERVICE v1.0 · Laravel 12 + Livewire 3</span>
    </div>

</div>
