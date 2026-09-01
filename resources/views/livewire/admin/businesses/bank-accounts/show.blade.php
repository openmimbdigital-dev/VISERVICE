<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.business-bank-accounts.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Datos bancarios</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $bank_account->bank_name }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Negocios</p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $bank_account->bank_name }}</h1>
                    @if($bank_account->is_default)
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">Predeterminada</span>
                    @endif
                </div>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.business-bank-accounts.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">Volver</a>
                @can('business_bank_accounts.edit')
                @if($can_edit)
                <a href="{{ route('admin.business-bank-accounts.index', ['edit' => $bank_account->id]) }}" wire:navigate class="btn btn-primary btn-sm flex-1 sm:flex-none justify-center">Editar</a>
                @endif
                @endcan
                @can('business_bank_accounts.delete')
                @if($can_delete)
                <button type="button" wire:click="deleteRecord" class="btn btn-danger btn-sm flex-1 sm:flex-none justify-center">Eliminar</button>
                @endif
                @endcan
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Datos de la cuenta</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Negocio</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $bank_account->business?->name ?? '—' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Banco</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $bank_account->bank_name }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Tipo de cuenta</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $bank_account->accountTypeLabel() }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Número de cuenta</dt>
                    <dd class="font-mono text-sm text-slate-900 sm:col-span-2">{{ $bank_account->account_number }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Titular</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $bank_account->account_holder }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">NIT</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $bank_account->document_number }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Estado</dt>
                    <dd class="sm:col-span-2">{{ $bank_account->active ? 'Activa' : 'Inactiva' }}</dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Vista previa en documento</h2>
            </div>
            <div class="px-5 py-5 text-sm text-slate-700">
                <p class="mb-3 text-center font-serif italic text-slate-600">¡Gracias por su confianza!</p>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Datos bancarios</p>
                    <div class="grid grid-cols-1 gap-2 text-xs sm:grid-cols-2">
                        <div><span class="text-slate-500">Banco:</span> {{ $bank_account->bank_name }}</div>
                        <div><span class="text-slate-500">Tipo:</span> {{ $bank_account->accountTypeLabel() }}</div>
                        <div><span class="text-slate-500">Cuenta:</span> {{ $bank_account->account_number }}</div>
                        <div><span class="text-slate-500">Titular:</span> {{ $bank_account->account_holder }}</div>
                        <div class="sm:col-span-2"><span class="text-slate-500">NIT:</span> {{ $bank_account->document_number }}</div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
