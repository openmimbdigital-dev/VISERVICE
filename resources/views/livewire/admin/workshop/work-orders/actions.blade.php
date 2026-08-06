<div class="flex flex-nowrap items-center justify-end gap-1">
    @can('workshop.work-orders.view')
    <a href="{{ route('admin.workshop.work-orders.show', $id) }}" wire:navigate title="Ver detalle"
        class="inline-flex shrink-0 rounded p-1.5 text-slate-400 transition-colors hover:bg-indigo-50 hover:text-indigo-600">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
    </a>
    <a href="{{ route('admin.workshop.work-orders.print', $id) }}" target="_blank" title="Imprimir PDF"
        class="inline-flex shrink-0 rounded p-1.5 text-slate-400 transition-colors hover:bg-indigo-50 hover:text-indigo-600">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
    </a>
    @endcan

    @can('workshop.work-orders.edit')
    @if($can_mutate)
    <a href="{{ route('admin.workshop.work-orders.form.edit', $id) }}" wire:navigate title="Editar"
        class="inline-flex shrink-0 rounded p-1.5 text-slate-400 transition-colors hover:bg-indigo-50 hover:text-indigo-600">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
    </a>
    @else
    <button type="button" disabled title="Solo se pueden editar OTs creadas o en proceso"
        class="inline-flex shrink-0 rounded p-1.5 text-slate-400 cursor-not-allowed opacity-40">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
    </button>
    @endif
    @endcan

    @can('workshop.work-orders.delete')
    <button type="button"
        @if($can_mutate) wire:click="deleteRecord({{ $id }})" @endif
        @disabled(! $can_mutate)
        title="{{ $can_mutate ? 'Eliminar' : 'Solo se pueden eliminar OTs creadas o en proceso' }}"
        class="inline-flex shrink-0 rounded p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-slate-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
    </button>
    @endcan
</div>
