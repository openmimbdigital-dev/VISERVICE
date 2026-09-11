<div class="relative mx-auto w-full max-w-[90rem]">
    <nav class="mb-6 flex items-center gap-x-2 text-xs text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="transition hover:text-indigo-600">Inicio</a>
        <span>/</span>
        <span class="font-medium text-slate-700">Transacciones de Bold</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-stretch lg:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Pagos en línea</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Transacciones de Bold</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">
                    Todo lo cobrado por la pasarela: las suscripciones que nos pagan los negocios y las
                    facturas que les pagan sus clientes.
                </p>
            </div>

            <div class="grid shrink-0 grid-cols-2 gap-3 sm:max-w-md">
                <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total cobrado</p>
                    <p class="mt-1 text-xl font-bold text-slate-900">{{ col_money($totals['amount']) }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">{{ $totals['count'] }} transacción(es)</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700">Ingreso nuestro</p>
                    <p class="mt-1 text-xl font-bold text-emerald-900">{{ col_money($totals['ours']) }}</p>
                    <p class="mt-0.5 text-xs text-emerald-700">Suscripciones</p>
                </div>
            </div>
        </div>
    </header>

    @if($totals['to_settle'] > 0)
    {{-- El número que de verdad hay que vigilar: dinero de terceros que entró por
         nuestra cuenta porque su negocio no tenía llaves propias. --}}
    <div class="mb-6 flex flex-col gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-amber-900">Pendiente de girar a los negocios</p>
            <p class="mt-0.5 text-xs text-amber-800">
                Entró a la cuenta de la plataforma porque esos negocios no tienen llaves propias de Bold.
            </p>
        </div>
        <div class="text-right">
            <p class="text-xl font-bold text-amber-900">{{ col_money($totals['to_settle']) }}</p>
            <a href="{{ route('admin.bold-settings.index') }}" wire:navigate class="text-xs font-medium text-amber-800 underline">
                Configurar sus llaves
            </a>
        </div>
    </div>
    @endif

    {{-- Filtros --}}
    <section class="mb-6 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Desde</label>
                <input type="date" wire:model.live="from" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Hasta</label>
                <input type="date" wire:model.live="to" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Tipo</label>
                <select wire:model.live="type" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none">
                    <option value="">Todos</option>
                    <option value="subscription">Suscripciones</option>
                    <option value="invoice">Facturas de taller</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Cuenta</label>
                <select wire:model.live="account" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none">
                    <option value="">Todas</option>
                    <option value="platform">De la plataforma</option>
                    <option value="business">Del negocio</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Negocio</label>
                <select wire:model.live="business_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none">
                    <option value="">Todos</option>
                    @foreach($businesses as $business)
                    <option value="{{ $business->id }}">{{ $business->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-3 flex justify-end">
            <button type="button" wire:click="resetFilters" class="text-xs font-medium text-slate-500 underline transition hover:text-slate-700">
                Limpiar filtros
            </button>
        </div>
    </section>

    {{-- Listado --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/80 text-xs text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Fecha</th>
                        <th class="px-4 py-3 text-left font-medium">Negocio</th>
                        <th class="px-4 py-3 text-left font-medium">Concepto</th>
                        <th class="px-4 py-3 text-left font-medium">Documento</th>
                        <th class="px-4 py-3 text-left font-medium">Método</th>
                        <th class="px-4 py-3 text-left font-medium">Cuenta</th>
                        <th class="px-4 py-3 text-right font-medium">Monto</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $row)
                    <tr class="transition hover:bg-slate-50/60">
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                            {{ $row['paid_at']?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-slate-800">{{ $row['business'] }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $row['type'] === 'subscription' ? 'bg-violet-50 text-violet-700 ring-1 ring-violet-600/20' : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' }}">
                                {{ $row['type_label'] }}
                            </span>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $row['concept'] }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if($row['document_url'])
                            <a href="{{ $row['document_url'] }}" wire:navigate class="font-mono text-xs font-medium text-indigo-600 hover:underline">{{ $row['document'] }}</a>
                            @else
                            <span class="font-mono text-xs text-slate-700">{{ $row['document'] }}</span>
                            @endif

                            @if($row['related'])
                            <p class="mt-0.5 text-xs text-slate-500">
                                @if($row['related_url'])
                                <a href="{{ $row['related_url'] }}" wire:navigate class="hover:underline">{{ $row['related'] }}</a>
                                @else
                                {{ $row['related'] }}
                                @endif
                            </p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $row['method'] }}</td>
                        <td class="px-4 py-3">
                            @if($row['account'] === 'business')
                            <span class="text-xs text-slate-600">Del negocio</span>
                            @elseif($row['own_money'])
                            <span class="text-xs text-slate-600">Nuestra</span>
                            @else
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-amber-600/20" title="Dinero del negocio que entró a nuestra cuenta">
                                Por girar
                            </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-slate-900">
                            {{ col_money($row['amount']) }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" wire:click="toggleDetail('{{ $row['key'] }}')"
                                class="text-xs font-medium text-indigo-600 hover:underline">
                                {{ $detail_key === $row['key'] ? 'Ocultar' : 'Detalle' }}
                            </button>
                        </td>
                    </tr>

                    @if($detail_key === $row['key'])
                    <tr class="bg-slate-50/80">
                        <td colspan="8" class="px-4 py-4">
                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                                <dl class="space-y-1.5 text-xs">
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">Transacción en Bold</dt>
                                        <dd class="font-mono text-slate-800">{{ $row['transaction_id'] ?? '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">Referencia</dt>
                                        <dd class="font-mono text-slate-800">{{ $row['reference'] ?? '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-slate-500">Moneda</dt>
                                        <dd class="text-slate-800">{{ $row['currency'] }}</dd>
                                    </div>
                                </dl>

                                <div class="lg:col-span-2">
                                    <p class="mb-1.5 text-xs font-semibold text-slate-700">Avisos de Bold</p>
                                    @forelse($detail['events'] as $event)
                                    <p class="text-xs text-slate-600">
                                        {{ $event->received_at?->format('d/m H:i:s') }} ·
                                        {{ $event->type }} ·
                                        firma {{ $event->signature_valid ? 'válida' : 'inválida' }} ·
                                        {{ $event->response_status }} en {{ $event->duration_ms }}ms
                                        <span class="text-slate-500">— {{ \Illuminate\Support\Str::limit((string) $event->result, 80) }}</span>
                                    </p>
                                    @empty
                                    <p class="text-xs text-slate-500">Bold no envió ningún aviso de esta transacción.</p>
                                    @endforelse

                                    @if($detail['checks']->isNotEmpty())
                                    <p class="mb-1.5 mt-3 text-xs font-semibold text-slate-700">Consultas a Bold</p>
                                    @foreach($detail['checks'] as $check)
                                    <p class="text-xs text-slate-600">
                                        {{ $check->created_at?->format('d/m H:i:s') }} ·
                                        {{ $check->origin }} · {{ $check->reported_status ?? '—' }} ·
                                        {{ $check->confirmed ? 'confirmó el pago' : 'no confirmó' }}
                                        <span class="text-slate-500">— {{ \Illuminate\Support\Str::limit((string) $check->decision, 80) }}</span>
                                    </p>
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-sm text-slate-500">
                            No hay transacciones de Bold en este rango.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
