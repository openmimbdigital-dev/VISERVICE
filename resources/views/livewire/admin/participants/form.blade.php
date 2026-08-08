<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">{{ org_term('Negocios') }}</span>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.participants.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Participantes</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $is_editing ? 'Editar' : 'Nuevo' }}</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">{{ org_term('Negocios') }}</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                    {{ $is_editing ? 'Editar participante' : 'Nuevo participante' }}
                </h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    {{ $is_editing
                        ? 'Actualiza los datos del participante.'
                        : 'Registra un nuevo participante en el directorio.' }}
                </p>
            </div>
            <a href="{{ route('admin.participants.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">Volver</a>
        </div>
    </header>

    <form wire:submit="save" class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Datos del participante</h2>
        </div>

        <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2">
            @if($is_super_admin)
                <div class="relative sm:col-span-2">
                    <label class="label-up">Negocio *</label>
                    <select wire:model.live="form.business_id" class="form-select w-full border px-3 py-2 text-sm @error('form.business_id') border-rose-400 @enderror">
                        <option value="">Seleccionar negocio</option>
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}">{{ $business->name }}</option>
                        @endforeach
                    </select>
                    @error('form.business_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            @endif

            <div class="relative">
                <label class="label-up">Nombre *</label>
                <input type="text" wire:model="form.first_name" class="form-input w-full border px-3 py-2 text-sm @error('form.first_name') border-rose-400 @enderror" />
                @error('form.first_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="relative">
                <label class="label-up">Apellido *</label>
                <input type="text" wire:model="form.last_name" class="form-input w-full border px-3 py-2 text-sm @error('form.last_name') border-rose-400 @enderror" />
                @error('form.last_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="relative">
                <label class="label-up">Rol</label>
                <select wire:model="form.participant_role_id" class="form-select w-full border px-3 py-2 text-sm @error('form.participant_role_id') border-rose-400 @enderror">
                    <option value="">Sin rol</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('form.participant_role_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-col justify-end pb-1">
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Estado</label>
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="$toggle('form.status')"
                        @class([
                            'relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200',
                            'bg-indigo-600' => $form->status,
                            'bg-slate-300' => ! $form->status,
                        ])>
                        <span @class([
                            'inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200',
                            'translate-x-6' => $form->status,
                            'translate-x-1' => ! $form->status,
                        ])></span>
                    </button>
                    <span class="text-sm text-slate-600">{{ $form->status ? 'Activo' : 'Inactivo' }}</span>
                </div>
            </div>

            <div class="relative">
                <label class="label-up">Tipo de documento</label>
                <select wire:model="form.document_type" class="form-select w-full border px-3 py-2 text-sm @error('form.document_type') border-rose-400 @enderror">
                    <option value="">—</option>
                    @foreach($document_types as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('form.document_type')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="relative">
                <label class="label-up">Número de documento</label>
                <input type="number" wire:model="form.document_number" class="form-input w-full border px-3 py-2 text-sm @error('form.document_number') border-rose-400 @enderror" />
                @error('form.document_number')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="relative">
                <label class="label-up">Teléfono</label>
                <input type="text" wire:model="form.phone_number" class="form-input w-full border px-3 py-2 text-sm @error('form.phone_number') border-rose-400 @enderror" />
                @error('form.phone_number')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="relative">
                <label class="label-up">Email</label>
                <input type="email" wire:model="form.email" class="form-input w-full border px-3 py-2 text-sm @error('form.email') border-rose-400 @enderror" />
                @error('form.email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="relative">
                <label class="label-up">Ciudad</label>
                <select wire:model="form.city_id" class="form-select w-full border px-3 py-2 text-sm @error('form.city_id') border-rose-400 @enderror">
                    <option value="">—</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}">{{ $city->name }}{{ $city->state_province ? ' — '.$city->state_province : '' }}</option>
                    @endforeach
                </select>
                @error('form.city_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="relative">
                <label class="label-up">País</label>
                <select wire:model="form.country_id" class="form-select w-full border px-3 py-2 text-sm @error('form.country_id') border-rose-400 @enderror">
                    <option value="">—</option>
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                    @endforeach
                </select>
                @error('form.country_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="relative sm:col-span-2">
                <label class="label-up">Dirección</label>
                <input type="text" wire:model="form.address" class="form-input w-full border px-3 py-2 text-sm @error('form.address') border-rose-400 @enderror" />
                @error('form.address')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end sm:gap-3">
            <a href="{{ route('admin.participants.index') }}" wire:navigate class="btn btn-outline-secondary w-full justify-center sm:w-auto">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary w-full justify-center disabled:opacity-60 sm:w-auto">
                <span wire:loading.remove wire:target="save">{{ $is_editing ? 'Guardar cambios' : 'Crear participante' }}</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>
        </div>
    </form>
</div>
