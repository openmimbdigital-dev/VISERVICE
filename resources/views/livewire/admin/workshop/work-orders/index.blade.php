<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Taller</span>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Órdenes de Trabajo</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Taller</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Órdenes de Trabajo</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Crea OTs directas o desde cotizaciones aceptadas. Gestiona ítems, estado y cierre de la orden.</p>
            </div>
            <div class="flex w-full shrink-0 flex-col gap-3 sm:w-auto">
                <div class="flex w-full flex-col gap-2 sm:flex-row sm:justify-end">
                    @can('workshop.work-orders.associated-documents.view')
                    <a href="{{ route('admin.workshop.work-orders.associated-documents.index') }}" wire:navigate
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 sm:w-auto">
                        Documentos asociados
                    </a>
                    @endcan
                    @can('workshop.work-orders.create')
                    <x-ui.create-button :href="route('admin.workshop.work-orders.form')" class="w-full justify-center sm:w-auto">
                        Nueva OT
                    </x-ui.create-button>
                    @endcan
                </div>
                {{-- Tarjetas por estado: además de informar, filtran --}}
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5 sm:max-w-xl">
                    @foreach([
                        ['label'=>'Borrador','value'=>$stats['borradores'],'color'=>'text-amber-700','ring'=>'ring-amber-500','value_key'=>'draft'],
                        ['label'=>'Creadas','value'=>$stats['creadas'],'color'=>'text-blue-600','ring'=>'ring-blue-500','value_key'=>'created'],
                        ['label'=>'En proceso','value'=>$stats['en_proceso'],'color'=>'text-yellow-600','ring'=>'ring-yellow-500','value_key'=>'in_progress'],
                        ['label'=>'Finalizadas','value'=>$stats['finalizadas'],'color'=>'text-emerald-600','ring'=>'ring-emerald-500','value_key'=>'completed'],
                        ['label'=>'Canceladas','value'=>$stats['canceladas'],'color'=>'text-red-600','ring'=>'ring-red-500','value_key'=>'cancelled'],
                    ] as $s)
                    @php $is_active = $status === $s['value_key']; @endphp
                    <button type="button" wire:click="filterByStatus('{{ $s['value_key'] }}')"
                        aria-pressed="{{ $is_active ? 'true' : 'false' }}"
                        title="{{ $is_active ? 'Quitar el filtro de estado' : 'Filtrar por «'.$s['label'].'»' }}"
                        class="rounded-xl border bg-white p-2.5 text-left shadow-sm transition hover:-translate-y-px hover:shadow
                            {{ $is_active ? 'border-transparent ring-2 '.$s['ring'] : 'border-slate-200/90 ring-1 ring-slate-900/[0.04] hover:border-slate-300' }}">
                        <p class="text-[10px] font-semibold uppercase tracking-wider {{ $is_active ? 'text-slate-700' : 'text-slate-500' }}">{{ $s['label'] }}</p>
                        <p class="mt-0.5 text-xl font-semibold tabular-nums {{ $s['color'] }}">{{ $s['value'] }}</p>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-baseline gap-2">
                    <h2 class="font-semibold text-slate-800">Listado de OTs</h2>
                    <span class="text-xs text-slate-500">
                        {{ $total_filtered }} {{ $total_filtered === 1 ? 'orden' : 'órdenes' }}
                        @if($active_filters > 0) con los filtros aplicados @endif
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    @if($active_filters > 0)
                    <button type="button" wire:click="resetFilters"
                        class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-500 transition hover:bg-slate-200/60 hover:text-slate-700">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Limpiar
                    </button>
                    @endif

                    <button type="button" wire:click="$toggle('show_filters')"
                        aria-expanded="{{ $show_filters ? 'true' : 'false' }}"
                        class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-xs font-semibold transition
                            {{ $show_filters || $active_filters > 0 ? 'border-indigo-200 bg-indigo-50 text-indigo-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filtros
                        @if($active_filters > 0)
                        <span class="inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-indigo-600 px-1 text-[10px] font-bold text-white">{{ $active_filters }}</span>
                        @endif
                        <svg class="h-3 w-3 transition-transform {{ $show_filters ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </div>
            </div>

            @if($show_filters)
            <div class="mt-4 grid grid-cols-1 gap-3 border-t border-slate-200/70 pt-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-2 lg:col-span-1">
                    <label for="wo-filter-client" class="mb-1.5 block text-xs font-medium text-slate-600">Cliente</label>
                    <select id="wo-filter-client" wire:model.live="client_id"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">Todos los clientes</option>
                        @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="wo-filter-delivery" class="mb-1.5 block text-xs font-medium text-slate-600">Entrega estimada</label>
                    <select id="wo-filter-delivery" wire:model.live="delivery"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        @foreach($delivery_options as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="wo-filter-billing" class="mb-1.5 block text-xs font-medium text-slate-600">Facturación</label>
                    <select id="wo-filter-billing" wire:model.live="billing"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        @foreach($billing_options as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <span class="mb-1.5 block text-xs font-medium text-slate-600">Creadas entre</span>
                    <div class="flex items-center gap-2">
                        <input type="date" wire:model.live="date_from" aria-label="Fecha desde"
                            class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <span class="shrink-0 text-xs text-slate-400">y</span>
                        <input type="date" wire:model.live="date_to" aria-label="Fecha hasta"
                            class="w-full rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                </div>
            </div>
            @endif

            {{-- Resumen de lo aplicado, para no tener que abrir el panel --}}
            @if($active_filters > 0)
            <div class="mt-3 flex flex-wrap items-center gap-1.5">
                @if($status !== '')
                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-700">
                    Estado: {{ \App\Enums\WorkOrderStatus::tryFrom($status)?->label() ?? $status }}
                    <button type="button" wire:click="$set('status', '')" class="text-slate-400 transition hover:text-slate-700" aria-label="Quitar filtro de estado">&times;</button>
                </span>
                @endif
                @if($client_id)
                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-700">
                    Cliente: {{ $clients->firstWhere('id', $client_id)?->name ?? $client_id }}
                    <button type="button" wire:click="$set('client_id', null)" class="text-slate-400 transition hover:text-slate-700" aria-label="Quitar filtro de cliente">&times;</button>
                </span>
                @endif
                @if($delivery !== '')
                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-700">
                    {{ $delivery_options[$delivery] ?? $delivery }}
                    <button type="button" wire:click="$set('delivery', '')" class="text-slate-400 transition hover:text-slate-700" aria-label="Quitar filtro de entrega">&times;</button>
                </span>
                @endif
                @if($billing !== '')
                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-700">
                    {{ $billing_options[$billing] ?? $billing }}
                    <button type="button" wire:click="$set('billing', '')" class="text-slate-400 transition hover:text-slate-700" aria-label="Quitar filtro de facturación">&times;</button>
                </span>
                @endif
                @if($date_from !== '' || $date_to !== '')
                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-700">
                    @if($date_from !== '' && $date_to !== '')
                        {{ \Carbon\Carbon::parse($date_from)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($date_to)->format('d/m/Y') }}
                    @elseif($date_from !== '')
                        Desde {{ \Carbon\Carbon::parse($date_from)->format('d/m/Y') }}
                    @else
                        Hasta {{ \Carbon\Carbon::parse($date_to)->format('d/m/Y') }}
                    @endif
                    <button type="button" wire:click="$set('date_from', ''); $set('date_to', '')" class="text-slate-400 transition hover:text-slate-700" aria-label="Quitar filtro de fechas">&times;</button>
                </span>
                @endif
            </div>
            @endif
        </div>

        <div class="overflow-x-auto p-3 sm:p-4">
            <livewire:admin.workshop.work-orders.datatable-work-orders
                :status_filter="$status"
                :client_filter="$client_id"
                :date_from_filter="$date_from"
                :date_to_filter="$date_to"
                :delivery_filter="$delivery"
                :billing_filter="$billing"
                :key="'work-orders-datatable-'.$filter_key" />
        </div>
    </section>
</div>
