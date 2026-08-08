<div class="relative">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <header class="mb-8">
        <div class="min-w-0 border-l-4 border-indigo-600 pl-4 sm:pl-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Registro público</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Registro de participante</h1>
            <p class="mt-2 max-w-xl text-sm text-slate-600">
                Completa el formulario para registrarte en <strong>{{ $business_name }}</strong>.
            </p>
        </div>
    </header>

    @if($submitted)
        <div class="mb-6 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-600/10">
            Registro completado. Puedes enviar otro formulario si lo necesitas.
        </div>
    @endif

    <form wire:submit="save" class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Datos del participante</h2>
        </div>

        <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2">
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

            <div class="relative">
                <label class="label-up">Teléfono</label>
                <input type="text" wire:model="form.phone_number" class="form-input w-full border px-3 py-2 text-sm @error('form.phone_number') border-rose-400 @enderror" />
                @error('form.phone_number')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
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
                <input type="text" inputmode="numeric" wire:model="form.document_number" class="form-input w-full border px-3 py-2 text-sm @error('form.document_number') border-rose-400 @enderror" />
                @error('form.document_number')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="relative sm:col-span-2">
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
            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary w-full justify-center disabled:opacity-60 sm:w-auto">
                <span wire:loading.remove wire:target="save">Enviar registro</span>
                <span wire:loading wire:target="save">Enviando...</span>
            </button>
        </div>
    </form>
</div>
