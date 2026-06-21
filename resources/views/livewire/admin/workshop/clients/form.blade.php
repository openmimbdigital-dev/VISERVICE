<div class="relative mx-auto w-full max-w-[90rem]">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex items-center gap-x-2 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">Taller</span>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.workshop.clients.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Clientes</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">{{ $is_editing ? 'Editar cliente' : 'Nuevo cliente' }}</span>
    </nav>

    <header class="mb-8">
        <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Taller</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                {{ $is_editing ? 'Editar cliente' : 'Nuevo cliente' }}
            </h1>
            <p class="mt-2 max-w-xl text-sm text-slate-600">
                {{ $is_editing
                    ? 'Actualiza los datos del cliente en el directorio del taller.'
                    : 'Registra un nuevo cliente en el directorio del taller.' }}
            </p>
        </div>
    </header>

    <form wire:submit="save" class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Datos del cliente</h2>
        </div>

        <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
            <div class="relative md:col-span-2">
                <label class="label-up">Nombre / Razón social *</label>
                <input type="text" wire:model="form.name" class="form-input w-full border px-3 py-2 text-sm" />
                @error('form.name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="relative">
                <label class="label-up">Tipo documento *</label>
                <select wire:model="form.document_type" class="form-select w-full border px-3 py-2 text-sm">
                    @foreach(['CC', 'NIT', 'CE', 'PA', 'PPT', 'TI'] as $tipo)
                        <option value="{{ $tipo }}">{{ $tipo }}</option>
                    @endforeach
                </select>
                @error('form.document_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="relative">
                <label class="label-up">Número documento</label>
                <input type="text" wire:model="form.document_number" class="form-input w-full border px-3 py-2 text-sm" />
                @error('form.document_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="relative">
                <label class="label-up">Teléfono</label>
                <input type="text" wire:model="form.phone" class="form-input w-full border px-3 py-2 text-sm" />
                @error('form.phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="relative">
                <label class="label-up">Email</label>
                <input type="email" wire:model="form.email" class="form-input w-full border px-3 py-2 text-sm" />
                @error('form.email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="relative md:col-span-2">
                <label class="label-up">Dirección</label>
                <input type="text" wire:model="form.address" class="form-input w-full border px-3 py-2 text-sm" />
                @error('form.address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="relative">
                <label class="label-up">Contacto</label>
                <input type="text" wire:model="form.contact_name" class="form-input w-full border px-3 py-2 text-sm" placeholder="Persona de contacto" />
                @error('form.contact_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-col justify-end pb-1">
                <label class="mb-1.5 block text-xs font-medium text-slate-700">Estado</label>
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="$toggle('form.status')"
                        @class([
                            'relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200',
                            'bg-indigo-600' => $form->status,
                            'bg-slate-300'  => ! $form->status,
                        ])>
                        <span @class([
                            'inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200',
                            'translate-x-6' => $form->status,
                            'translate-x-1' => ! $form->status,
                        ])></span>
                    </button>
                    <span @class([
                        'text-sm',
                        'font-medium text-emerald-700' => $form->status,
                        'text-slate-500'               => ! $form->status,
                    ])>
                        {{ $form->status ? 'Cliente activo' : 'Cliente inactivo' }}
                    </span>
                </div>
                @error('form.status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="relative md:col-span-2">
                <label class="label-up">Notas</label>
                <textarea wire:model="form.notes" class="form-input w-full border px-3 py-2 text-sm" rows="3"></textarea>
                @error('form.notes')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
            <a href="{{ route('admin.workshop.clients.index') }}" wire:navigate class="btn btn-outline-secondary">
                Cancelar
            </a>
            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary">
                {{ $is_editing ? 'Actualizar cliente' : 'Guardar cliente' }}
            </button>
        </div>
    </form>
</div>
