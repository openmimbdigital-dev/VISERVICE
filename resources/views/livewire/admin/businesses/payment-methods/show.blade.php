<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.business-payment-methods.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Métodos de pago</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $payment_method->name }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Negocios</p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $payment_method->name }}</h1>
                    @if($payment_method->general)
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">General</span>
                    @endif
                    @if($payment_method->is_default)
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">Predeterminado</span>
                    @endif
                </div>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.business-payment-methods.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Volver</a>
                @can('business_payment_methods.edit')
                @if($can_edit)
                <a href="{{ route('admin.business-payment-methods.index', ['edit' => $payment_method->id]) }}" wire:navigate class="btn btn-primary btn-sm flex-1 sm:flex-none justify-center">Editar</a>
                @elseif($is_general_readonly)
                <button type="button" disabled title="Método general del sistema: no editable" class="btn btn-primary btn-sm flex-1 sm:flex-none justify-center disabled:opacity-50">Editar</button>
                @endif
                @endcan
                @can('business_payment_methods.delete')
                @if($can_delete)
                <button type="button" wire:click="deleteRecord" class="btn btn-danger btn-sm flex-1 sm:flex-none justify-center">Eliminar</button>
                @elseif($is_general_readonly)
                <button type="button" disabled title="Método general del sistema: no se puede eliminar" class="btn btn-danger btn-sm flex-1 sm:flex-none justify-center disabled:opacity-50">Eliminar</button>
                @endif
                @endcan
            </div>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Información</h2>
        </div>
        <dl class="divide-y divide-slate-100 px-5 py-2">
            <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                <dt class="text-xs font-medium text-slate-500">General</dt>
                <dd class="text-sm text-slate-900 sm:col-span-2">{{ $payment_method->general ? 'Sí — aplica a todos los negocios' : 'No' }}</dd>
            </div>
            <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                <dt class="text-xs font-medium text-slate-500">Negocio</dt>
                <dd class="text-sm text-slate-900 sm:col-span-2">{{ $payment_method->business?->name ?? '—' }}</dd>
            </div>
            <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                <dt class="text-xs font-medium text-slate-500">Orden</dt>
                <dd class="text-sm text-slate-900 sm:col-span-2">{{ $payment_method->sort_order }}</dd>
            </div>
            <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                <dt class="text-xs font-medium text-slate-500">Estado</dt>
                <dd class="sm:col-span-2">{{ $payment_method->active ? 'Activo' : 'Inactivo' }}</dd>
            </div>
        </dl>
    </section>
</div>
