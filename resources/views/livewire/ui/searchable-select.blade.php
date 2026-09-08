<div
    class="relative"
    x-data="{ open: false }"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false"
>
    <button
        type="button"
        x-on:click="@if(! $disabled) open = ! open; if (open) $nextTick(() => $refs.search?.focus()) @endif"
        @disabled($disabled)
        class="flex w-full items-center justify-between gap-2 rounded-xl border bg-slate-50 px-3.5 py-2.5 text-left text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:opacity-60 {{ $invalid ? 'border-rose-400 bg-rose-50' : 'border-slate-200' }}"
        aria-haspopup="listbox"
        x-bind:aria-expanded="open"
    >
        <span class="min-w-0 flex-1 truncate {{ $selected_label ? 'text-slate-800' : 'text-slate-400' }}">
            {{ $selected_label ?: $placeholder }}
        </span>
        <svg class="h-4 w-4 shrink-0 text-slate-400 transition" x-bind:class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.origin.top
        class="absolute z-30 mt-1 w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg ring-1 ring-slate-900/5"
        role="listbox"
    >
        <div class="border-b border-slate-100 p-2">
            <input
                type="text"
                x-ref="search"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ $searchPlaceholder }}"
                autocomplete="off"
                x-on:keydown.enter.prevent
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
            >
        </div>

        <ul class="max-h-56 overflow-y-auto py-1">
            @forelse($options as $option)
            <li>
                <button
                    type="button"
                    wire:click="select('{{ $option['value'] }}')"
                    x-on:click="open = false"
                    class="flex w-full flex-col px-3 py-2 text-left hover:bg-indigo-50 {{ (string) $value === $option['value'] ? 'bg-indigo-50' : '' }}"
                >
                    <span class="truncate text-sm text-slate-800">{{ $option['label'] }}</span>
                    @if($option['hint'] !== '')
                    <span class="truncate text-xs text-slate-500">{{ $option['hint'] }}</span>
                    @endif
                </button>
            </li>
            @empty
            @if($can_create)
            <li class="p-2">
                <button
                    type="button"
                    wire:click="openCreateModal"
                    x-on:click="open = false"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ $create_button }}
                </button>
            </li>
            @elseif($is_searching)
            <li class="px-3 py-3 text-sm text-slate-400">{{ $emptyText }}</li>
            @else
            <li class="px-3 py-3 text-sm text-slate-400">No hay registros</li>
            @endif
            @endforelse
        </ul>
    </div>

    @if($showCreateModal && $create_component)
        @teleport('body')
            @livewire($create_component, ['search' => $search], key('searchable-create-'.$create_component.'-'.$search))
        @endteleport
    @endif
</div>
