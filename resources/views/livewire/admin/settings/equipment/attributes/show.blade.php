<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.settings.equipment.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Configuración</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.settings.equipment.attributes.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Atributos</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $attribute->name }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Configuración · Equipos</p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $attribute->name }}</h1>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $attribute->general ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/20' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20' }}">
                        {{ $attribute->general ? 'General' : 'Por comercio' }}
                    </span>
                </div>
                <p class="mt-2 text-sm text-slate-600">{{ $attribute->typeLabel() }}</p>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.settings.equipment.attributes.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 sm:flex-none justify-center">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </a>
                @if($can_edit)
                <a href="{{ route('admin.settings.equipment.attributes.edit', $attribute) }}" wire:navigate class="btn btn-primary btn-sm flex-1 sm:flex-none justify-center">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar
                </a>
                @endif
                @if($can_delete)
                <button type="button" wire:click="deleteRecord"
                    class="btn btn-danger btn-sm flex-1 sm:flex-none justify-center">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Eliminar
                </button>
                @endif
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Información general</h2>
            </div>
            <dl class="divide-y divide-slate-100 px-5 py-2">
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Nombre</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $attribute->name }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Tipo</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $attribute->typeLabel() }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">General</dt>
                    <dd class="sm:col-span-2">
                        @if($attribute->general)
                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">Sí</span>
                        @else
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">No</span>
                        @endif
                    </dd>
                </div>
                @if($is_super_admin && ! $attribute->general)
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Comercios</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">
                        @if($attribute->businesses->isNotEmpty())
                            {{ $attribute->businesses->pluck('name')->join(', ') }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
                @endif
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Obligatorio</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $attribute->required ? 'Sí' : 'No' }}</dd>
                </div>
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Oculto en creación</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $attribute->nullable_creation ? 'Sí' : 'No' }}</dd>
                </div>
                @if($attribute->type === \App\Enums\AttributeType::NUMBER)
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Rango numérico</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">
                        @if($attribute->min_value !== null || $attribute->max_value !== null)
                            {{ $attribute->min_value ?? '—' }} – {{ $attribute->max_value ?? '—' }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
                @endif
                @if($attribute->type === \App\Enums\AttributeType::COLOR)
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Color predeterminado</dt>
                    <dd class="sm:col-span-2">
                        <span class="inline-flex items-center gap-2 text-sm text-slate-900">
                            <span class="inline-flex h-6 w-6 rounded-full border border-slate-200 ring-1 ring-slate-900/10"
                                style="background-color: {{ $attribute->options['default'] ?? '#6366f1' }}"></span>
                            <span class="font-mono uppercase">{{ $attribute->options['default'] ?? '—' }}</span>
                        </span>
                    </dd>
                </div>
                @endif
                <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="text-xs font-medium text-slate-500">Registrado</dt>
                    <dd class="text-sm text-slate-900 sm:col-span-2">{{ $attribute->created_at?->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Tipos de equipo</h2>
            </div>
            <div class="px-5 py-5">
                @if($equipment_types->isNotEmpty())
                    <ul class="space-y-2">
                        @foreach($equipment_types as $equipment_type)
                            <li class="rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-sm text-slate-700">
                                {{ $equipment_type->name }}
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-slate-500">Sin tipos de equipo asociados.</p>
                @endif

                @if($is_general_readonly)
                <p class="mt-4 rounded-xl border border-indigo-100 bg-indigo-50 px-3.5 py-2.5 text-xs text-indigo-800">
                    Atributo general del sistema. Puedes consultarlo pero no editarlo ni eliminarlo.
                </p>
                @endif
            </div>
        </section>
    </div>

    @if(in_array($attribute->type->value, ['select', 'radio', 'checkbox'], true) && ! empty($attribute->options))
    <section class="mt-4 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Opciones</h2>
        </div>
        <ul class="divide-y divide-slate-100 px-5 py-2">
            @foreach($attribute->options as $option)
                <li class="py-2.5 text-sm text-slate-700">{{ $option['label'] ?? $option['value'] ?? '—' }}</li>
            @endforeach
        </ul>
    </section>
    @endif
</div>
