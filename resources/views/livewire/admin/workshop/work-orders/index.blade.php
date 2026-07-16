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
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 sm:max-w-md">
                    @foreach([
                        ['label'=>'Abiertas','value'=>$stats['abiertas'],'color'=>'text-blue-600'],
                        ['label'=>'En proceso','value'=>$stats['en_proceso'],'color'=>'text-yellow-600'],
                        ['label'=>'Finalizadas','value'=>$stats['finalizadas'],'color'=>'text-emerald-600'],
                        ['label'=>'Canceladas','value'=>$stats['canceladas'],'color'=>'text-red-600'],
                    ] as $s)
                    <div class="rounded-xl border border-slate-200/90 bg-white p-2.5 shadow-sm ring-1 ring-slate-900/[0.04]">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ $s['label'] }}</p>
                        <p class="mt-0.5 text-xl font-semibold tabular-nums {{ $s['color'] }}">{{ $s['value'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <h2 class="font-semibold text-slate-800">Listado de OTs</h2>
        </div>
        <div class="overflow-x-auto p-3 sm:p-4">
            <livewire:admin.workshop.work-orders.datatable-work-orders />
        </div>
    </section>
</div>
