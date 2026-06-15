<div class="p-6 space-y-6">

    {{-- Datos para gráficas (accesibles desde JS antes de Alpine) --}}
    <script>
        window._fd = {
            months:        @json($chartMonths),
            revenue:       @json($chartRevenue),
            planLabels:    @json($planLabels),
            planValues:    @json($planValues),
            statusLabels:  @json($statusLabels),
            statusValues:  @json($statusValues),
            statusColors:  @json($statusColors),
            cycleLabels:   @json($cycleLabels),
            cycleCounts:   @json($cycleCounts),
            cycleRevenue:  @json($cycleRevenue),
        };
    </script>

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Panel Financiero</h1>
            <p class="text-sm text-slate-500 mt-1">Resumen de ingresos, suscripciones y cobros.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 bg-slate-100 px-3 py-1.5 rounded-full">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
            Datos en tiempo real
        </span>
    </div>

    {{-- KPIs fila 1 --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Total cobrado --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total cobrado</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900">${{ number_format($totalCobrado, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Histórico acumulado</p>
        </div>

        {{-- Este mes --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Este mes</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900">${{ number_format($estesMes, 0, ',', '.') }}</p>
            <div class="flex items-center gap-1.5 mt-1">
                @if($mesVariacion > 0)
                    <span class="inline-flex items-center gap-0.5 text-xs font-medium text-emerald-600">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        +{{ $mesVariacion }}%
                    </span>
                @elseif($mesVariacion < 0)
                    <span class="inline-flex items-center gap-0.5 text-xs font-medium text-red-500">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        {{ $mesVariacion }}%
                    </span>
                @else
                    <span class="text-xs text-slate-400">Sin cambio</span>
                @endif
                <span class="text-xs text-slate-400">vs. mes anterior</span>
            </div>
        </div>

        {{-- Este año --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Este año</span>
                <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900">${{ number_format($esteAnio, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Año {{ now()->year }}</p>
        </div>

        {{-- Suscripciones activas --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Suscripciones</span>
                <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900">{{ $activeSubs }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $negociosActivos }} negocios activos</p>
        </div>
    </div>

    {{-- KPIs fila 2 --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Pendiente de cobro --}}
        <div class="bg-white rounded-2xl border border-amber-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Pendiente</span>
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900">${{ number_format($pendiente, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">En facturas pendientes</p>
        </div>

        {{-- Cartera vencida --}}
        <div class="bg-white rounded-2xl border {{ $vencido > 0 ? 'border-red-200' : 'border-slate-200' }} p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold {{ $vencido > 0 ? 'text-red-500' : 'text-slate-500' }} uppercase tracking-wide">Cartera vencida</span>
                <div class="w-8 h-8 rounded-lg {{ $vencido > 0 ? 'bg-red-100' : 'bg-slate-100' }} flex items-center justify-center">
                    <svg class="w-4 h-4 {{ $vencido > 0 ? 'text-red-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold {{ $vencido > 0 ? 'text-red-600' : 'text-slate-900' }}">${{ number_format($vencido, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Facturas con plazo superado</p>
        </div>

        {{-- Ticket promedio --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Ticket promedio</span>
                <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900">${{ number_format($ticketPromedio, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Por factura pagada</p>
        </div>

        {{-- Mes anterior --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Mes anterior</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900">${{ number_format($mesPasado, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ ucfirst(now()->subMonth()->locale('es')->isoFormat('MMMM YYYY')) }}</p>
        </div>
    </div>

    {{-- Gráfica principal + donut planes --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

        {{-- Ingresos por mes --}}
        <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Ingresos cobrados</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Últimos 12 meses</p>
                </div>
            </div>
            <div
                x-data
                x-init="
                    const d = window._fd;
                    new ApexCharts($refs.chartRevenue, {
                        chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: 'inherit', sparkline: { enabled: false } },
                        series: [{ name: 'Ingresos', data: d.revenue }],
                        xaxis: { categories: d.months, labels: { style: { fontSize: '11px', colors: '#94a3b8' } }, axisBorder: { show: false }, axisTicks: { show: false } },
                        yaxis: { labels: { formatter: v => '$' + new Intl.NumberFormat('es-CO').format(v), style: { fontSize: '11px', colors: '#94a3b8' } } },
                        colors: ['#6366f1'],
                        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
                        stroke: { curve: 'smooth', width: 2.5 },
                        grid: { borderColor: '#f1f5f9', strokeDashArray: 4, padding: { left: 0, right: 0 } },
                        dataLabels: { enabled: false },
                        tooltip: { y: { formatter: v => '$' + new Intl.NumberFormat('es-CO').format(v) } },
                    }).render()
                "
            >
                <div x-ref="chartRevenue"></div>
            </div>
        </div>

        {{-- Distribución por plan --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="mb-4">
                <h3 class="text-sm font-semibold text-slate-900">Por plan</h3>
                <p class="text-xs text-slate-400 mt-0.5">Suscripciones activas</p>
            </div>
            @if(count($planValues) > 0)
            <div
                x-data
                x-init="
                    const d = window._fd;
                    new ApexCharts($refs.chartPlans, {
                        chart: { type: 'donut', height: 260, fontFamily: 'inherit' },
                        series: d.planValues,
                        labels: d.planLabels,
                        colors: ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4'],
                        legend: { position: 'bottom', fontSize: '12px', markers: { size: 6 } },
                        dataLabels: { enabled: true, formatter: (v, opts) => opts.w.config.series[opts.seriesIndex] },
                        plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0) } } } } },
                        stroke: { width: 0 },
                        tooltip: { y: { formatter: v => v + ' suscripciones' } },
                    }).render()
                "
            >
                <div x-ref="chartPlans"></div>
            </div>
            @else
            <div class="h-64 flex flex-col items-center justify-center text-center">
                <p class="text-sm text-slate-400">Sin suscripciones activas</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Estado suscripciones + ciclos de facturación --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

        {{-- Estado --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="mb-4">
                <h3 class="text-sm font-semibold text-slate-900">Estado de suscripciones</h3>
                <p class="text-xs text-slate-400 mt-0.5">Todas las suscripciones</p>
            </div>
            @if(count($statusValues) > 0)
            <div
                x-data
                x-init="
                    const d = window._fd;
                    new ApexCharts($refs.chartStatus, {
                        chart: { type: 'donut', height: 260, fontFamily: 'inherit' },
                        series: d.statusValues,
                        labels: d.statusLabels,
                        colors: d.statusColors,
                        legend: { position: 'bottom', fontSize: '12px', markers: { size: 6 } },
                        dataLabels: { enabled: true, formatter: (v, opts) => opts.w.config.series[opts.seriesIndex] },
                        plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0) } } } } },
                        stroke: { width: 0 },
                        tooltip: { y: { formatter: v => v + ' suscripciones' } },
                    }).render()
                "
            >
                <div x-ref="chartStatus"></div>
            </div>
            @else
            <div class="h-64 flex items-center justify-center">
                <p class="text-sm text-slate-400">Sin datos</p>
            </div>
            @endif
        </div>

        {{-- Ciclos de facturación --}}
        <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="mb-4">
                <h3 class="text-sm font-semibold text-slate-900">Ciclos de facturación</h3>
                <p class="text-xs text-slate-400 mt-0.5">Suscripciones activas por ciclo</p>
            </div>
            @if(count($cycleCounts) > 0)
            <div
                x-data
                x-init="
                    const d = window._fd;
                    new ApexCharts($refs.chartCycles, {
                        chart: { type: 'bar', height: 260, toolbar: { show: false }, fontFamily: 'inherit' },
                        series: [
                            { name: 'Suscripciones', data: d.cycleCounts },
                            { name: 'Ingresos (ciclo)', data: d.cycleRevenue },
                        ],
                        xaxis: { categories: d.cycleLabels, labels: { style: { fontSize: '12px', colors: '#94a3b8' } }, axisBorder: { show: false }, axisTicks: { show: false } },
                        yaxis: [
                            { title: { text: 'Suscripciones', style: { fontSize: '11px', color: '#94a3b8' } }, labels: { style: { fontSize: '11px', colors: '#94a3b8' } } },
                            { opposite: true, title: { text: 'Ingresos', style: { fontSize: '11px', color: '#94a3b8' } }, labels: { formatter: v => '$' + new Intl.NumberFormat('es-CO').format(v), style: { fontSize: '11px', colors: '#94a3b8' } } },
                        ],
                        colors: ['#6366f1', '#10b981'],
                        grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                        dataLabels: { enabled: false },
                        plotOptions: { bar: { columnWidth: '50%', borderRadius: 6 } },
                        legend: { position: 'top', fontSize: '12px' },
                        tooltip: {
                            y: [
                                { formatter: v => v + ' suscripciones' },
                                { formatter: v => '$' + new Intl.NumberFormat('es-CO').format(v) },
                            ]
                        },
                    }).render()
                "
            >
                <div x-ref="chartCycles"></div>
            </div>
            @else
            <div class="h-64 flex items-center justify-center">
                <p class="text-sm text-slate-400">Sin suscripciones activas</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Ingresos por cuenta y efectivo --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-900">Ingresos cobrados por destino</h3>
            <p class="text-xs text-slate-400 mt-0.5">Total acumulado de facturas pagadas según donde se recibió el dinero</p>
        </div>

        @php
            $grandTotal = $byAccount->sum('total') + $cashTotal;
        @endphp

        <div class="divide-y divide-slate-50">

            {{-- Por cuenta bancaria --}}
            @foreach($byAccount as $row)
            @php
                $account  = $row->bankAccount;
                $bank     = $account?->bank;
                $logoUrl  = $account?->logo
                    ? Storage::disk('public')->url($account->logo)
                    : ($bank?->logo ? Storage::disk('public')->url($bank->logo) : null);
                $pct      = $grandTotal > 0 ? round(($row->total / $grandTotal) * 100, 1) : 0;
            @endphp
            <div class="px-5 py-4 flex items-center gap-4">
                {{-- Logo banco --}}
                <div class="w-10 h-10 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center shrink-0 overflow-hidden">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $bank?->name }}" class="w-full h-full object-contain p-0.5">
                    @else
                        <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                        </svg>
                    @endif
                </div>
                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-900 truncate">
                                {{ $bank?->name ?? 'Sin banco' }}
                            </p>
                            <p class="text-xs text-slate-400 truncate">
                                {{ $account?->account_type_label }} · {{ $account?->account_number }}
                                @if($account?->account_holder) · {{ $account->account_holder }} @endif
                            </p>
                        </div>
                        <div class="text-right ml-4 shrink-0">
                            <p class="text-sm font-bold text-slate-900">${{ number_format($row->total, 0, ',', '.') }}</p>
                            <p class="text-xs text-slate-400">{{ $row->invoices }} {{ $row->invoices == 1 ? 'factura' : 'facturas' }}</p>
                        </div>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">{{ $pct }}% del total cobrado</p>
                </div>
            </div>
            @endforeach

            {{-- Efectivo / sin cuenta asignada --}}
            @php
                $cashPct = $grandTotal > 0 ? round(($cashTotal / $grandTotal) * 100, 1) : 0;
            @endphp
            <div class="px-5 py-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl border border-emerald-200 bg-emerald-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1.5">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Efectivo / sin cuenta asignada</p>
                            <p class="text-xs text-slate-400">Pagos confirmados sin cuenta de destino registrada</p>
                        </div>
                        <div class="text-right ml-4 shrink-0">
                            <p class="text-sm font-bold text-slate-900">${{ number_format($cashTotal, 0, ',', '.') }}</p>
                            <p class="text-xs text-slate-400">{{ $cashCount }} {{ $cashCount == 1 ? 'factura' : 'facturas' }}</p>
                        </div>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="bg-emerald-400 h-1.5 rounded-full" style="width: {{ $cashPct }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">{{ $cashPct }}% del total cobrado</p>
                </div>
            </div>

            @if($byAccount->isEmpty() && $cashTotal == 0)
            <div class="px-5 py-10 text-center text-sm text-slate-400">
                Aún no hay facturas pagadas registradas.
            </div>
            @endif
        </div>
    </div>

    {{-- Tablas --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

        {{-- Últimas facturas pagadas --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Últimas facturas cobradas</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Las más recientes</p>
                </div>
                <a href="{{ route('admin.payments.index') }}" wire:navigate
                    class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">Ver todas →</a>
            </div>
            @if($recentPaid->isEmpty())
            <div class="py-10 text-center text-sm text-slate-400">Sin facturas pagadas</div>
            @else
            <div class="divide-y divide-slate-50">
                @foreach($recentPaid as $inv)
                <div class="px-5 py-3 flex items-center justify-between gap-3 hover:bg-slate-50 transition-colors">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-900 truncate">{{ $inv->business?->name ?? '—' }}</p>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-xs text-slate-400">{{ $inv->invoice_number }}</span>
                            @if($inv->subscription?->plan)
                            <span class="text-xs text-slate-300">·</span>
                            <span class="text-xs text-slate-400">{{ $inv->subscription->plan->name }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-semibold text-emerald-600">${{ number_format($inv->amount, 0, ',', '.') }}</p>
                        <p class="text-xs text-slate-400">{{ $inv->paid_at?->locale('es')->isoFormat('D MMM YY') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Cobros pendientes --}}
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Cobros pendientes</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Ordenados por fecha de vencimiento</p>
                </div>
                @if($pendingInvoices->isNotEmpty())
                <span class="text-xs font-semibold bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full">
                    {{ $pendingInvoices->count() }} facturas
                </span>
                @endif
            </div>
            @if($pendingInvoices->isEmpty())
            <div class="py-10 text-center">
                <p class="text-sm text-slate-400">Sin cobros pendientes</p>
                <p class="text-xs text-slate-300 mt-1">¡Todo al día!</p>
            </div>
            @else
            <div class="divide-y divide-slate-50">
                @foreach($pendingInvoices as $inv)
                @php $isVencida = $inv->due_date->isPast(); @endphp
                <div class="px-5 py-3 flex items-center justify-between gap-3 hover:bg-slate-50 transition-colors">
                    <div class="min-w-0 flex items-center gap-2.5">
                        @if($isVencida)
                        <div class="w-1.5 h-1.5 rounded-full bg-red-400 shrink-0"></div>
                        @else
                        <div class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></div>
                        @endif
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-900 truncate">{{ $inv->business?->name ?? '—' }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-slate-400">{{ $inv->invoice_number }}</span>
                                @if($isVencida)
                                <span class="text-xs font-medium text-red-500">Vencida</span>
                                @else
                                <span class="text-xs text-slate-400">Vence {{ $inv->due_date->locale('es')->isoFormat('D MMM') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-semibold {{ $isVencida ? 'text-red-600' : 'text-amber-600' }}">${{ number_format($inv->amount, 0, ',', '.') }}</p>
                        @if($inv->subscription?->plan)
                        <p class="text-xs text-slate-400">{{ $inv->subscription->plan->name }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>
