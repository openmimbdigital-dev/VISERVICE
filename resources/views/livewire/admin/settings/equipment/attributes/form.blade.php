<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.settings.equipment.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Configuración</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.settings.equipment.attributes.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Atributos</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $form->isEditing() ? 'Editar' : 'Nuevo' }}</span>
    </nav>

    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Configuración · Equipos</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                {{ $form->isEditing() ? 'Editar atributo' : 'Crear atributo' }}
            </h1>
            <p class="mt-2 max-w-xl text-sm text-slate-600">
                Define campos personalizados y asígnalos a uno o más tipos de equipo.
            </p>
        </div>
        <a href="{{ route('admin.settings.equipment.attributes.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm shrink-0">Volver</a>
    </header>

    <form wire:submit="save" class="space-y-6">
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Información básica</h2>
            </div>
            <div class="space-y-5 p-5">
                @if($is_super_admin)
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" wire:model.live="form.general" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Atributo general (disponible para todos los comercios)
                    </label>

                    @if(! $form->general)
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Comercios <span class="text-rose-500">*</span></label>
                            <p class="mb-3 text-sm text-slate-600">Selecciona uno o más comercios a los que aplicará este atributo.</p>
                            @if($businesses->isNotEmpty())
                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach($businesses as $business)
                                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 transition hover:bg-slate-50">
                                            <input type="checkbox" value="{{ $business->id }}" wire:model.live="form.business_ids"
                                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-sm text-slate-700">{{ $business->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-slate-500">No hay comercios activos disponibles.</p>
                            @endif
                            @error('form.business_ids') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    @endif
                @endif
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Nombre <span class="text-rose-500">*</span></label>
                        <input wire:model="form.name" type="text" placeholder="Ej. Kilometraje"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.name') border-rose-400 bg-rose-50 @enderror">
                        @error('form.name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Tipo <span class="text-rose-500">*</span></label>
                        <select wire:model.live="form.type"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.type') border-rose-400 bg-rose-50 @enderror">
                            <option value="">Selecciona un tipo</option>
                            <option value="text">Texto</option>
                            <option value="number">Número</option>
                            <option value="textarea">Área de texto</option>
                            <option value="select">Lista desplegable</option>
                            <option value="radio">Botones de radio</option>
                            <option value="checkbox">Casillas de verificación</option>
                            <option value="color">Color</option>
                        </select>
                        @error('form.type') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                @if(in_array($form->type, ['select', 'radio', 'checkbox'], true))
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-medium text-slate-800">Opciones</h3>
                        <button type="button" wire:click="addOption" class="btn btn-outline-primary btn-sm">Agregar opción</button>
                    </div>
                    @forelse($form->options as $index => $option)
                    <div class="mb-2 flex items-center gap-2">
                        <input wire:model="form.options.{{ $index }}.label" type="text" placeholder="Etiqueta"
                            class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <button type="button" wire:click="removeOption({{ $index }})" class="btn btn-outline-danger btn-sm">Quitar</button>
                    </div>
                    @empty
                    <p class="text-sm text-slate-500">Sin opciones. Agrega al menos una.</p>
                    @endforelse
                </div>
                @endif

                @if($form->type === 'color')
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Color predeterminado <span class="text-rose-500">*</span></label>
                    <p class="mb-3 text-sm text-slate-600">Valor inicial que se usará al registrar equipos con este atributo.</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <input wire:model.live="form.default_color" type="color"
                            class="h-11 w-14 cursor-pointer rounded-lg border border-slate-200 bg-white p-1">
                        <input wire:model="form.default_color" type="text" placeholder="#6366f1" maxlength="7"
                            class="w-full max-w-[10rem] rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-mono text-sm uppercase focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('form.default_color') border-rose-400 bg-rose-50 @enderror">
                        <span class="inline-flex h-8 w-8 rounded-full border border-slate-200 ring-1 ring-slate-900/10"
                            style="background-color: {{ $form->default_color ?: '#6366f1' }}"></span>
                    </div>
                    @error('form.default_color') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                @endif

                @if($form->type === 'number')
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Valor mínimo</label>
                        <input wire:model="form.min_value" type="number" step="any"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        @error('form.min_value') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Valor máximo</label>
                        <input wire:model="form.max_value" type="number" step="any"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        @error('form.max_value') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                @endif

                <div class="flex flex-col gap-3 sm:flex-row sm:gap-6">
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" wire:model="form.required" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Campo obligatorio
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" wire:model="form.nullable_creation" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Ocultar en creación (solo en edición)
                    </label>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="font-semibold text-slate-800">Tipos de equipo <span class="text-rose-500">*</span></h2>
                <p class="mt-1 text-sm text-slate-600">Selecciona a qué tipos de equipo aplica este atributo.</p>
            </div>
            <div class="p-5">
                @if($equipment_types->isNotEmpty())
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($equipment_types as $equipment_type)
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 transition hover:bg-slate-50">
                        <input type="checkbox" value="{{ $equipment_type->id }}" wire:model="form.equipment_types"
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-slate-700">{{ $equipment_type->name }}</span>
                    </label>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-500">No hay tipos de equipo disponibles. Crea tipos en configuración primero.</p>
                @endif
                @error('form.equipment_types') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
        </section>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end sm:gap-3">
            <a href="{{ route('admin.settings.equipment.attributes.index') }}" wire:navigate class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary">
                <span wire:loading.remove wire:target="save">{{ $form->isEditing() ? 'Actualizar atributo' : 'Crear atributo' }}</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>
        </div>
    </form>
</div>
