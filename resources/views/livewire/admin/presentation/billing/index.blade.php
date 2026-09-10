<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Presentación</span>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Facturación</span>
    </nav>

    <header class="mb-8">
        <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Facturación</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Gestión de facturación</h1>
            <p class="mt-2 max-w-xl text-sm text-slate-600">Módulo de demostración para emitir facturas, registrar pagos y administrar conceptos de cobro.</p>
        </div>
    </header>

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Facturas</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-slate-900">{{ $stats['invoices'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Pagadas</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-emerald-600">{{ $stats['paid'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Pendientes</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-amber-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Conceptos</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-indigo-600">{{ $stats['concepts'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <a href="{{ route('admin.presentation.billing.invoices.index') }}" wire:navigate
            class="group overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035] transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Facturas</p>
                <h2 class="mt-1 text-base font-semibold text-slate-900">Emitir y consultar facturas</h2>
            </div>
            <p class="px-5 py-4 text-sm text-slate-600">Crea facturas de pensión, transporte u otros cobros y revisa su estado.</p>
            <div class="border-t border-slate-100 px-5 py-3">
                <span class="text-xs font-semibold text-indigo-600 group-hover:underline">Ir a facturas</span>
            </div>
        </a>

        <a href="{{ route('admin.presentation.billing.payments.index') }}" wire:navigate
            class="group overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035] transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Pagos</p>
                <h2 class="mt-1 text-base font-semibold text-slate-900">Registrar y ver pagos</h2>
            </div>
            <p class="px-5 py-4 text-sm text-slate-600">Consulta los abonos recibidos y registra un pago desde cada factura.</p>
            <div class="border-t border-slate-100 px-5 py-3">
                <span class="text-xs font-semibold text-indigo-600 group-hover:underline">Ir a pagos</span>
            </div>
        </a>

        <a href="{{ route('admin.presentation.billing.concepts.index') }}" wire:navigate
            class="group overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035] transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-600">Conceptos</p>
                <h2 class="mt-1 text-base font-semibold text-slate-900">Conceptos de cobro</h2>
            </div>
            <p class="px-5 py-4 text-sm text-slate-600">Administra pensión, transporte, alimentación y otros valores del colegio.</p>
            <div class="border-t border-slate-100 px-5 py-3">
                <span class="text-xs font-semibold text-indigo-600 group-hover:underline">Ir a conceptos</span>
            </div>
        </a>
    </div>
</div>
