<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.presentation.billing.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Facturación</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.presentation.billing.invoices.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Facturas</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Nueva factura</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Facturación</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Nueva factura</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Emite un cobro de demostración. No se guarda en base de datos.</p>
            </div>
            <a href="{{ route('admin.presentation.billing.invoices.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">Volver</a>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
            <div class="flex-1 space-y-4 px-4 py-5 sm:px-6">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Familia inscrita</label>
                    <select wire:model.live="family_id"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">Seleccionar</option>
                        @foreach($families as $family)
                        <option value="{{ $family['id'] }}">{{ $family['child_name'] }} · {{ $family['parent_name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Estudiante <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="student"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('student') border-rose-400 bg-rose-50 @enderror">
                        @error('student') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Acudiente <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="guardian"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('guardian') border-rose-400 bg-rose-50 @enderror">
                        @error('guardian') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Concepto <span class="text-rose-500">*</span></label>
                        <select wire:model.live="concept"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('concept') border-rose-400 bg-rose-50 @enderror">
                            @foreach($concepts as $row)
                            <option value="{{ $row['name'] }}">{{ $row['name'] }} ({{ col_money($row['amount']) }})</option>
                            @endforeach
                        </select>
                        @error('concept') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Valor <span class="text-rose-500">*</span></label>
                        <input type="number" min="1" wire:model="amount"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('amount') border-rose-400 bg-rose-50 @enderror">
                        @error('amount') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Vencimiento <span class="text-rose-500">*</span></label>
                        <input type="date" wire:model="due_date"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('due_date') border-rose-400 bg-rose-50 @enderror">
                        @error('due_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Estado</label>
                        <select wire:model="status"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            @foreach($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
                <a href="{{ route('admin.presentation.billing.invoices.index') }}" wire:navigate class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-center text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</a>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">Guardar</button>
            </div>
        </form>
    </section>
</div>
