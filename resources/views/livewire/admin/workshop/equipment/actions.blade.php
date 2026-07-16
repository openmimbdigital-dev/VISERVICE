<div class="flex flex-wrap items-center justify-end gap-1">
    @can('workshop.equipment.view')
    <a href="{{ route('admin.workshop.equipment.show', [$equipment_type_id, $id]) }}" wire:navigate title="Ver equipo"
        class="rounded p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
    </a>
    @endcan
    @can('workshop.equipment.edit')
    <a href="{{ route('admin.workshop.equipment.form.edit', [$equipment_type_id, $id]) }}" wire:navigate title="Editar equipo"
        class="rounded p-1.5 text-slate-400 transition-colors hover:bg-indigo-50 hover:text-indigo-600">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
    </a>
    @endcan
    @can('workshop.equipment.delete')
    <button type="button" wire:click="deleteRecord({{ $id }})"
        @disabled(! ($can_delete ?? false))
        title="{{ ($can_delete ?? false) ? 'Eliminar' : ($delete_block_reason ?? 'No se puede eliminar') }}"
        class="rounded p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-slate-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
    </button>
    @endcan
</div>
