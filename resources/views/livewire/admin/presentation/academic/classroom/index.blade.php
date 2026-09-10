@php
    $color_map = [
        'indigo' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'ring' => 'ring-indigo-600/20', 'bar' => 'bg-indigo-500'],
        'sky' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'ring' => 'ring-sky-600/20', 'bar' => 'bg-sky-500'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-600/20', 'bar' => 'bg-emerald-500'],
        'violet' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-700', 'ring' => 'ring-violet-600/20', 'bar' => 'bg-violet-500'],
        'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'ring' => 'ring-amber-600/20', 'bar' => 'bg-amber-500'],
        'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'ring' => 'ring-rose-600/20', 'bar' => 'bg-rose-500'],
        'teal' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-700', 'ring' => 'ring-teal-600/20', 'bar' => 'bg-teal-500'],
        'fuchsia' => ['bg' => 'bg-fuchsia-50', 'text' => 'text-fuchsia-700', 'ring' => 'ring-fuchsia-600/20', 'bar' => 'bg-fuchsia-500'],
    ];
@endphp

<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('admin.presentation.dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Presentación</span>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Mi aula</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Académico</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Mi aula</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">Asignaturas de demostración. Filtra por jornada, sección y miss. Las nuevas asignaturas solo viven en esta sesión.</p>
            </div>
            <x-ui.create-button wire:click="openCreate" class="w-full justify-center sm:w-auto">
                Nueva asignatura
            </x-ui.create-button>
        </div>
    </header>

    <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-semibold text-slate-800">Filtros</h2>
                @if($active_filters > 0)
                <button type="button" wire:click="resetFilters"
                    class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-500 transition hover:bg-slate-200/60 hover:text-slate-700">
                    Limpiar
                </button>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-3 sm:px-5">
            <div>
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Jornada</label>
                <select wire:model.live="filter_shift"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">Todas</option>
                    @foreach($shifts as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Sección</label>
                <select wire:model.live="filter_section"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">Todas</option>
                    @foreach($sections as $section)
                    <option value="{{ $section }}">{{ $section }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Miss</label>
                <select wire:model.live="filter_teacher"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">Todas</option>
                    @foreach($teachers as $teacher)
                    <option value="{{ $teacher }}">{{ $teacher }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    @if(count($subjects) === 0)
    <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
        <p class="text-sm font-medium text-slate-700">No hay asignaturas con esos filtros</p>
        <p class="mt-1 text-sm text-slate-500">Cambia jornada, sección o miss, o crea una asignatura nueva.</p>
    </div>
    @else
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($subjects as $subject)
        @php $tone = $color_map[$subject['color'] ?? 'indigo'] ?? $color_map['indigo']; @endphp
        <a href="{{ route('admin.presentation.academic.classroom.show', $subject['id']) }}" wire:navigate
            class="group overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035] transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
            <div class="h-1.5 {{ $tone['bar'] }}"></div>
            <div class="px-5 py-5">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="text-base font-semibold text-slate-900">{{ $subject['name'] }}</h3>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $tone['bg'] }} {{ $tone['text'] }} {{ $tone['ring'] }}">
                        {{ $subject['section'] }}
                    </span>
                </div>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Jornada</dt>
                        <dd class="font-medium text-slate-800">{{ $subject['shift_label'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Miss</dt>
                        <dd class="font-medium text-slate-800">{{ $subject['teacher'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-slate-500">Horario</dt>
                        <dd class="text-right text-slate-700">{{ $subject['schedule'] }}</dd>
                    </div>
                </dl>
            </div>
            <div class="flex items-center justify-between border-t border-slate-100 px-5 py-3">
                <p class="text-xs text-slate-500">{{ $subject['activities_count'] }} {{ $subject['activities_count'] === 1 ? 'actividad' : 'actividades' }}</p>
                <span class="text-xs font-semibold text-indigo-600 group-hover:underline">Ver actividades</span>
            </div>
        </a>
        @endforeach
    </div>
    @endif

    @if($showModal)
    <x-ui.modal centered maxWidth="lg">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <h3 class="text-base font-semibold text-slate-900">Nueva asignatura</h3>
            <button type="button" wire:click="closeModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form wire:submit="saveSubject" class="flex min-h-0 flex-1 flex-col">
            <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Nombre <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="new_name"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('new_name') border-rose-400 bg-rose-50 @enderror">
                    @error('new_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Jornada <span class="text-rose-500">*</span></label>
                        <select wire:model="new_shift"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('new_shift') border-rose-400 bg-rose-50 @enderror">
                            @foreach($shifts as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('new_shift') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Sección <span class="text-rose-500">*</span></label>
                        <select wire:model="new_section"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('new_section') border-rose-400 bg-rose-50 @enderror">
                            @foreach($sections as $section)
                            <option value="{{ $section }}">{{ $section }}</option>
                            @endforeach
                        </select>
                        @error('new_section') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Miss <span class="text-rose-500">*</span></label>
                    <select wire:model="new_teacher"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('new_teacher') border-rose-400 bg-rose-50 @enderror">
                        @foreach($teachers as $teacher)
                        <option value="{{ $teacher }}">{{ $teacher }}</option>
                        @endforeach
                    </select>
                    @error('new_teacher') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Horario</label>
                    <input type="text" wire:model="new_schedule" placeholder="Ej. Lun · Mié  8:00 – 9:30"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('new_schedule') border-rose-400 bg-rose-50 @enderror">
                    @error('new_schedule') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
                <button type="button" wire:click="closeModal" class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60 sm:w-auto">Guardar</button>
            </div>
        </form>
    </x-ui.modal>
    @endif
</div>
