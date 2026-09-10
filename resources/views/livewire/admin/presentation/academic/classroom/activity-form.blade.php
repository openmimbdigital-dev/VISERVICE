<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.presentation.academic.classroom.show', $subject['id']) }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">{{ $subject['name'] }}</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.presentation.academic.classroom.activities.index', [$subject['id'], $activity_type]) }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">{{ $type_label }}</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Nueva</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">{{ $subject['name'] }}</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Nueva {{ mb_strtolower($type_label) }}</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Completa los datos. El tipo ya está definido por el flujo que elegiste.</p>
            </div>
            <a href="{{ route('admin.presentation.academic.classroom.activities.index', [$subject['id'], $activity_type]) }}" wire:navigate class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">Volver</a>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
            <div class="flex-1 space-y-4 px-4 py-5 sm:px-6">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Tipo</label>
                    <input type="text" value="{{ $type_label }}" disabled class="w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-600">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Título <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="title" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('title') border-rose-400 bg-rose-50 @enderror">
                    @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Fecha de entrega <span class="text-rose-500">*</span></label>
                        <input type="date" wire:model="due_date" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('due_date') border-rose-400 bg-rose-50 @enderror">
                        @error('due_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Nota máxima <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.1" min="1" max="5" wire:model="max_score" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('max_score') border-rose-400 bg-rose-50 @enderror">
                        @error('max_score') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Descripción</label>
                    <textarea wire:model="description" rows="4" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20"></textarea>
                </div>
            </div>
            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
                <a href="{{ route('admin.presentation.academic.classroom.activities.index', [$subject['id'], $activity_type]) }}" wire:navigate class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-center text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</a>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">Guardar</button>
            </div>
        </form>
    </section>
</div>
