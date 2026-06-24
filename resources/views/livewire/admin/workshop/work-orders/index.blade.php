<div class="relative mx-auto w-full max-w-[90rem]">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex items-center gap-x-2 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Taller</span>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Órdenes de Trabajo</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-stretch lg:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Taller</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Órdenes de Trabajo</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Gestión completa de OTs. Controla servicios, repuestos, remisiones, facturas y órdenes de compra.</p>
            </div>
            <div class="grid shrink-0 grid-cols-4 gap-3 sm:max-w-lg">
                @foreach([['label'=>'Abiertas','value'=>$stats['abiertas'],'color'=>'text-blue-600'],['label'=>'En proceso','value'=>$stats['en_proceso'],'color'=>'text-yellow-600'],['label'=>'Finalizadas','value'=>$stats['finalizadas'],'color'=>'text-emerald-600'],['label'=>'Canceladas','value'=>$stats['canceladas'],'color'=>'text-red-600']] as $s)
                <div class="rounded-2xl border border-slate-200/90 bg-white p-3 shadow-sm ring-1 ring-slate-900/[0.04]">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ $s['label'] }}</p>
                    <p class="mt-1 text-2xl font-semibold tabular-nums {{ $s['color'] }}">{{ $s['value'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Listado de OTs</h2>
            <button wire:click="openCreate" class="btn btn-primary btn-sm">
                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva OT directa
            </button>
        </div>
        <div class="p-4">
            <livewire:admin.workshop.work-orders.datatable-work-orders />
        </div>
    </section>

    {{-- Modal nueva OT directa --}}
    <div x-show="$wire.showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl" @click.stop>
            <h3 class="text-lg font-semibold text-slate-900">Nueva OT Directa</h3>
            <p class="mt-1 text-sm text-slate-500">Crea la OT directamente sin pasar por cotización.</p>
            <div class="mt-4 grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="label-up">Cliente *</label>
                    <select wire:model.live="client_id" class="form-select">
                        <option value="">Seleccionar cliente</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('client_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="col-span-2">
                    <label class="label-up">Equipo *</label>
                    <select wire:model="equipment_id" class="form-select" {{ !$client_id ? 'disabled' : '' }}>
                        <option value="">{{ $client_id ? 'Seleccionar equipo' : 'Primero selecciona un cliente' }}</option>
                        @foreach($equipment_for_client as $e)
                            <option value="{{ $e->id }}">{{ $e->plate }} — {{ $e->brand }} {{ $e->model }}</option>
                        @endforeach
                    </select>
                    @error('equipment_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label-up">Km al ingreso</label>
                    <input type="number" wire:model="km_entry" class="form-input" min="0" />
                </div>
                <div>
                    <label class="label-up">Entrega estimada</label>
                    <input type="date" wire:model="estimated_delivery" class="form-input" />
                </div>
                <div>
                    <label class="label-up">IVA (%)</label>
                    <input type="number" wire:model="tax_percentage" class="form-input" min="0" max="100" step="0.5" />
                </div>
                <div class="col-span-2">
                    <label class="label-up">Diagnóstico inicial</label>
                    <textarea wire:model="diagnosis" class="form-input" rows="2" placeholder="Problema reportado"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="closeModal" class="btn btn-outline-secondary">Cancelar</button>
                <button wire:click="save" wire:loading.attr="disabled" class="btn btn-primary">Crear OT</button>
            </div>
        </div>
    </div>
</div>
