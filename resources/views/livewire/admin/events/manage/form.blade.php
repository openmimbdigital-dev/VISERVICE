<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.events.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Gestión de eventos</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.events.manage.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Administrar eventos</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.events.manage.category.index', $event_category) }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">{{ $event_category->name }}</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $form->isEditing() ? 'Editar' : 'Nuevo' }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">{{ $event_category->name }}</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                    {{ $form->isEditing() ? 'Editar evento' : 'Nuevo evento' }}
                </h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    @if($is_periodic)
                        Categoría periódica: elige días de la semana o fechas específicas para crear los eventos.
                    @else
                        Define los datos principales del evento.
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.events.manage.category.index', $event_category) }}" wire:navigate class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">Volver</a>
        </div>
    </header>

    <form wire:submit="save" class="space-y-6">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-4 sm:px-5">
                <h2 class="font-semibold text-slate-800">Información del evento</h2>
            </div>
            <div class="space-y-5 p-4 sm:p-5">
                @if($is_super_admin)
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Iglesia <span class="text-rose-500">*</span></label>
                        <select wire:model.live="form.business_id"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.business_id') border-rose-400 bg-rose-50 @enderror">
                            <option value="">Selecciona una iglesia</option>
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </select>
                        @error('form.business_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Nombre <span class="text-rose-500">*</span></label>
                        <input wire:model="form.name" type="text" placeholder="Ej. Culto dominical, Conferencia de jóvenes"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.name') border-rose-400 bg-rose-50 @enderror">
                        @error('form.name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    @if($is_periodic)
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Cómo definir las fechas <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border px-3.5 py-3 text-sm transition {{ $form->schedule_mode === 'weekdays' ? 'border-indigo-300 bg-indigo-50/60 text-indigo-900' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-indigo-200 hover:bg-indigo-50/40' }}">
                                    <input type="radio" wire:model.live="form.schedule_mode" value="weekdays"
                                        class="mt-0.5 border-slate-300 text-indigo-600 focus:ring-indigo-500/30">
                                    <span>
                                        <span class="block font-medium">Días de la semana</span>
                                        <span class="mt-0.5 block text-xs opacity-80">Genera eventos por mes inicio/fin y días elegidos.</span>
                                    </span>
                                </label>
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border px-3.5 py-3 text-sm transition {{ $form->schedule_mode === 'specific_dates' ? 'border-indigo-300 bg-indigo-50/60 text-indigo-900' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-indigo-200 hover:bg-indigo-50/40' }}">
                                    <input type="radio" wire:model.live="form.schedule_mode" value="specific_dates"
                                        class="mt-0.5 border-slate-300 text-indigo-600 focus:ring-indigo-500/30">
                                    <span>
                                        <span class="block font-medium">Fechas específicas</span>
                                        <span class="mt-0.5 block text-xs opacity-80">Elige días concretos en el calendario del mes.</span>
                                    </span>
                                </label>
                            </div>
                            @error('form.schedule_mode') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Año <span class="text-rose-500">*</span></label>
                            <select wire:model.live="form.year"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.year') border-rose-400 bg-rose-50 @enderror">
                                <option value="">Selecciona</option>
                                @foreach($year_options as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('form.year') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        @if($form->schedule_mode === 'weekdays')
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-slate-700">Mes inicio <span class="text-rose-500">*</span></label>
                                <select wire:model="form.start_month"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.start_month') border-rose-400 bg-rose-50 @enderror">
                                    <option value="">Selecciona</option>
                                    @foreach($month_options as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('form.start_month') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-slate-700">Mes fin <span class="text-rose-500">*</span></label>
                                <select wire:model="form.end_month"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.end_month') border-rose-400 bg-rose-50 @enderror">
                                    <option value="">Selecciona</option>
                                    @foreach($month_options as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('form.end_month') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-xs font-medium text-slate-700">Días de la semana <span class="text-rose-500">*</span></label>
                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                    @foreach($weekday_options as $value => $label)
                                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/40">
                                            <input type="checkbox" wire:model="form.weekdays" value="{{ $value }}"
                                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/30">
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                                @error('form.weekdays') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                @error('form.weekdays.*') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        @else
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-slate-700">Mes <span class="text-rose-500">*</span></label>
                                <select wire:model.live="form.specific_month"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.specific_month') border-rose-400 bg-rose-50 @enderror">
                                    <option value="">Selecciona</option>
                                    @foreach($month_options as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('form.specific_month') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-1.5 block text-xs font-medium text-slate-700">Calendario <span class="text-rose-500">*</span></label>
                                <p class="mb-3 text-xs text-slate-500">Haz clic en los días para seleccionar las fechas de los eventos. Cada día crea un evento.</p>

                                @if($calendar_weeks === [])
                                    <p class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-sm text-slate-500">Selecciona año y mes para ver el calendario.</p>
                                @else
                                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                                        <div class="grid grid-cols-7 border-b border-slate-100 bg-slate-50/80 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                            @foreach($calendar_weekday_headers as $header)
                                                <div class="px-1 py-2">{{ $header }}</div>
                                            @endforeach
                                        </div>
                                        <div class="divide-y divide-slate-100">
                                            @foreach($calendar_weeks as $week)
                                                <div class="grid grid-cols-7">
                                                    @foreach($week as $day)
                                                        <div class="border-r border-slate-100 p-1 last:border-r-0">
                                                            @if($day['date'] === null)
                                                                <div class="flex h-10 items-center justify-center text-sm text-transparent">·</div>
                                                            @elseif($day['disabled'])
                                                                <div class="flex h-10 items-center justify-center rounded-lg text-sm text-slate-300">
                                                                    {{ $day['day'] }}
                                                                </div>
                                                            @else
                                                                <button type="button"
                                                                    wire:click="toggleSpecificDate('{{ $day['date'] }}')"
                                                                    class="flex h-10 w-full items-center justify-center rounded-lg text-sm font-medium transition {{ $day['selected'] ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                                                                    {{ $day['day'] }}
                                                                </button>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if(count($form->specific_dates) > 0)
                                    <p class="mt-2 text-xs text-slate-600">
                                        {{ count($form->specific_dates) }}
                                        {{ count($form->specific_dates) === 1 ? 'fecha seleccionada' : 'fechas seleccionadas' }}:
                                        {{ collect($form->specific_dates)->map(fn ($d) => \Illuminate\Support\Carbon::parse($d)->format('d/m'))->join(', ') }}
                                    </p>
                                @endif
                                @error('form.specific_dates') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                @error('form.specific_dates.*') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    @else
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Fecha de inicio <span class="text-rose-500">*</span></label>
                            <input wire:model.live="form.date_start" type="date"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.date_start') border-rose-400 bg-rose-50 @enderror">
                            @error('form.date_start') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Fecha de fin <span class="text-rose-500">*</span></label>
                            <input wire:model.live="form.date_end" type="date"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.date_end') border-rose-400 bg-rose-50 @enderror">
                            @error('form.date_end') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @unless($is_multi_day)
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Hora de inicio <span class="text-rose-500">*</span></label>
                            <input wire:model="form.start_time" type="time"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.start_time') border-rose-400 bg-rose-50 @enderror">
                            @error('form.start_time') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Hora de fin <span class="text-rose-500">*</span></label>
                            <input wire:model="form.end_time" type="time"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.end_time') border-rose-400 bg-rose-50 @enderror">
                            @error('form.end_time') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    @endunless

                    @if($is_multi_day)
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Horario por día <span class="text-rose-500">*</span></label>
                            <p class="mb-3 text-xs text-slate-500">Define la hora de inicio y fin para cada día del evento.</p>
                            <div class="space-y-2">
                                @foreach($form->day_schedules as $index => $schedule)
                                    <div class="grid grid-cols-1 gap-2 rounded-xl border border-slate-200 bg-slate-50/70 p-3 sm:grid-cols-3 sm:items-end">
                                        <div>
                                            <p class="mb-1.5 text-xs font-medium text-slate-700">Fecha</p>
                                            <p class="rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-medium text-slate-800">
                                                {{ \Illuminate\Support\Carbon::parse($schedule['date'])->format('d/m/Y') }}
                                                <span class="ml-1 text-xs font-normal text-slate-500">({{ \App\Enums\Weekday::labelFromDate($schedule['date']) }})</span>
                                            </p>
                                            <input type="hidden" wire:model="form.day_schedules.{{ $index }}.date">
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Hora inicio</label>
                                            <input wire:model="form.day_schedules.{{ $index }}.start_time" type="time"
                                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.day_schedules.'.$index.'.start_time') border-rose-400 bg-rose-50 @enderror">
                                            @error('form.day_schedules.'.$index.'.start_time') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Hora fin</label>
                                            <input wire:model="form.day_schedules.{{ $index }}.end_time" type="time"
                                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.day_schedules.'.$index.'.end_time') border-rose-400 bg-rose-50 @enderror">
                                            @error('form.day_schedules.'.$index.'.end_time') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('form.day_schedules') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Estado</label>
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3">
                            <div>
                                <p class="text-sm font-medium text-slate-800">{{ $form->active ? 'Activo' : 'Inactivo' }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">Indica si el evento está activo o inactivo.</p>
                            </div>
                            <button type="button" wire:click="$toggle('form.active')"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500/30 {{ $form->active ? 'bg-indigo-600' : 'bg-slate-300' }}">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $form->active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                        @error('form.active') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Controles del evento</label>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-sm text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/40">
                                <input type="checkbox" wire:model="form.attendance_enabled"
                                    class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/30">
                                <span>
                                    <span class="block font-medium text-slate-800">Toma de asistencia activa</span>
                                    <span class="mt-0.5 block text-xs text-slate-500">Permite registrar la asistencia del evento.</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-sm text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/40">
                                <input type="checkbox" wire:model="form.participation_enabled"
                                    class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/30">
                                <span>
                                    <span class="block font-medium text-slate-800">Toma de participación activa</span>
                                    <span class="mt-0.5 block text-xs text-slate-500">Permite registrar la participación del evento.</span>
                                </span>
                            </label>
                        </div>
                        @error('form.attendance_enabled') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        @error('form.participation_enabled') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Equipos</label>
                        @if($is_super_admin && ! $form->business_id)
                            <p class="text-sm text-slate-500">Selecciona una iglesia para ver sus equipos.</p>
                        @elseif($teams->isEmpty())
                            <p class="text-sm text-slate-500">No hay equipos activos disponibles para esta iglesia.</p>
                        @else
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                @foreach($teams as $team)
                                    <div class="flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 px-2 py-1.5 text-sm text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50/40">
                                        <label class="flex min-w-0 flex-1 cursor-pointer items-center gap-2 px-1 py-1">
                                            <input type="checkbox" wire:model="form.event_team_ids" value="{{ $team->id }}"
                                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/30">
                                            <span class="min-w-0 truncate">
                                                {{ $team->name }}
                                                @unless($team->active)
                                                    <span class="text-xs text-slate-400">(inactivo)</span>
                                                @endunless
                                            </span>
                                        </label>
                                        <button type="button"
                                            wire:click="openTeamDetail({{ $team->id }})"
                                            title="Ver roles e integrantes"
                                            class="shrink-0 rounded-lg p-2 text-slate-400 transition hover:bg-white hover:text-indigo-600">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span class="sr-only">Ver equipo</span>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @error('form.event_team_ids') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        @error('form.event_team_ids.*') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Descripción</label>
                        <textarea wire:model="form.description" rows="4" placeholder="Notas u observaciones del evento"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.description') border-rose-400 bg-rose-50 @enderror"></textarea>
                        @error('form.description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.events.manage.category.index', $event_category) }}" wire:navigate class="btn btn-outline-secondary w-full justify-center sm:w-auto">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary w-full justify-center disabled:opacity-60 sm:w-auto">
                <span wire:loading.remove wire:target="save">
                    @if($form->isEditing())
                        Guardar cambios
                    @elseif($is_periodic)
                        Crear eventos del período
                    @else
                        Crear evento
                    @endif
                </span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>
        </div>
    </form>

    @if($show_team_modal && $preview_team)
    <x-ui.modal centered maxWidth="lg">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeTeamDetail"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <div class="min-w-0">
                <h3 class="truncate text-base font-semibold text-slate-900">{{ $preview_team->name }}</h3>
                <p class="mt-0.5 text-xs text-slate-500">Roles, funciones e integrantes del equipo</p>
            </div>
            <button type="button" wire:click="closeTeamDetail" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex min-h-0 flex-1 flex-col">
            <div class="max-h-[60vh] flex-1 space-y-3 overflow-y-auto px-4 py-5 sm:px-6">
                @forelse($preview_team->roles as $role)
                    @php
                        $role_members = $preview_team->members->where('event_team_role_id', $role->id);
                    @endphp
                    <div class="rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                        <p class="text-sm font-semibold text-slate-900">{{ $role->name }}</p>
                        <p class="mt-1 text-xs leading-relaxed text-slate-500">
                            {{ $role->functions ?: 'Sin funciones definidas.' }}
                        </p>
                        <div class="mt-3 border-t border-slate-200/80 pt-3">
                            <p class="mb-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Usuarios asignados</p>
                            @forelse($role_members as $member)
                                <p class="text-sm text-slate-800">{{ $member->user?->full_name ?? '—' }}</p>
                            @empty
                                <p class="text-sm text-slate-400">Sin usuarios asignados a este rol.</p>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500">Este equipo no tiene roles configurados.</p>
                @endforelse
            </div>

            <div class="flex shrink-0 justify-end border-t border-slate-100 px-4 py-4 sm:px-6">
                <button type="button" wire:click="closeTeamDetail"
                    class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 sm:w-auto">
                    Cerrar
                </button>
            </div>
        </div>
    </x-ui.modal>
    @endif
</div>
