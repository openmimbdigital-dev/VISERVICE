<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.businesses.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Negocios</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Facturación electrónica</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Negocios</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Facturación electrónica DIAN</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">
                    Configura cada negocio como emisor ante la DIAN: resolución de facturación, rango autorizado
                    y perfil de emisión del proveedor tecnológico.
                </p>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <button type="button" wire:click="testConnection" wire:loading.attr="disabled" wire:target="testConnection"
                    class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                    <span wire:loading.remove wire:target="testConnection">Probar conexión</span>
                    <span wire:loading wire:target="testConnection">Probando...</span>
                </button>
                @if($can_create)
                <x-ui.create-button wire:click="openCreate" class="flex-1 justify-center sm:flex-none">Nueva configuración</x-ui.create-button>
                @endif
            </div>
        </div>
    </header>

    @forelse($settings as $setting)
    @php $missing = $setting->missingRequirements(); @endphp
    <section wire:key="dian-setting-{{ $setting->id }}" class="mb-4 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h2 class="font-semibold text-slate-800">{{ $setting->business?->name ?? 'Negocio' }}</h2>
                <p class="mt-0.5 text-xs text-slate-500">
                    NIT {{ $setting->business?->nit }} —
                    <span class="font-medium">{{ $setting->environment === 'production' ? 'Producción' : 'Pruebas' }}</span>
                </p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $missing === [] ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20' }}">
                    {{ $missing === [] ? 'Lista para emitir' : 'Configuración incompleta' }}
                </span>
                @if($can_edit)
                <button type="button" wire:click="openEdit({{ $setting->id }})" class="btn btn-outline-secondary btn-sm">Editar</button>
                <button type="button" wire:click="fetchResolution({{ $setting->id }})"
                    wire:loading.attr="disabled" wire:target="fetchResolution"
                    title="Consulta al proveedor el prefijo, el rango, la vigencia y la clave técnica de este NIT"
                    class="btn btn-outline-secondary btn-sm">
                    <span wire:loading.remove wire:target="fetchResolution">Traer resolución</span>
                    <span wire:loading wire:target="fetchResolution">Consultando...</span>
                </button>
                @if($setting->prefix)
                <button type="button" wire:click="syncConsecutive({{ $setting->id }})"
                    wire:loading.attr="disabled" wire:target="syncConsecutive"
                    title="Busca en el proveedor el último documento emitido con este prefijo y adelanta la numeración si viene atrasada"
                    class="btn btn-outline-secondary btn-sm">
                    <span wire:loading.remove wire:target="syncConsecutive">Traer consecutivo</span>
                    <span wire:loading wire:target="syncConsecutive">Consultando...</span>
                </button>
                @endif
                @if($setting->tr_tipo_id && ! $setting->credit_note_tr_tipo_id)
                <button type="button" wire:click="registerCreditNoteProfile({{ $setting->id }})"
                    wire:loading.attr="disabled" wire:target="registerCreditNoteProfile"
                    title="Crea en el proveedor el perfil con el que se emiten las notas credito de este negocio"
                    class="btn btn-outline-secondary btn-sm">
                    <span wire:loading.remove wire:target="registerCreditNoteProfile">Habilitar notas credito</span>
                    <span wire:loading wire:target="registerCreditNoteProfile">Registrando...</span>
                </button>
                @endif

                @if(! $setting->tr_tipo_id)
                <button type="button" wire:click="confirmRegistration({{ $setting->id }})"
                    wire:loading.attr="disabled" wire:target="registerWithProvider"
                    class="btn btn-primary btn-sm">
                    <span wire:loading.remove wire:target="registerWithProvider">Registrar ante el proveedor</span>
                    <span wire:loading wire:target="registerWithProvider">Registrando...</span>
                </button>
                @endif
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-x-6 gap-y-4 p-5 sm:grid-cols-4">
            <div>
                <p class="text-xs font-medium text-slate-500">Perfil de emisión</p>
                <p class="font-mono text-sm text-slate-900">{{ $setting->tr_tipo_id ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Resolución</p>
                <p class="font-mono text-sm text-slate-900">{{ $setting->resolution_number ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Prefijo y rango</p>
                <p class="font-mono text-sm text-slate-900">
                    {{ $setting->prefix ?? '—' }}
                    @if($setting->range_from) {{ $setting->range_from }}–{{ $setting->range_to }} @endif
                </p>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Próximo número</p>
                <p class="font-mono text-sm font-semibold text-indigo-700">
                    {{ $setting->prefix }}{{ $setting->upcomingConsecutive() ?: '—' }}
                </p>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Vigencia</p>
                <p class="text-sm text-slate-900">
                    @if($setting->valid_from && $setting->valid_to)
                        {{ $setting->valid_from->format('d/m/Y') }} – {{ $setting->valid_to->format('d/m/Y') }}
                    @else — @endif
                </p>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Clave técnica</p>
                {{-- Se muestran las puntas de la clave: es la única forma de confirmar
                     que quedó guardada la correcta sin exponerla completa. --}}
                @if(filled($setting->technical_key))
                <p class="font-mono text-sm text-slate-900" title="Clave técnica registrada (parcial)">
                    {{ Str::substr($setting->technical_key, 0, 6) }}…{{ Str::substr($setting->technical_key, -4) }}
                    <span class="font-sans text-xs text-slate-400">({{ Str::length($setting->technical_key) }} caracteres)</span>
                </p>
                @else
                <p class="text-sm text-slate-900">—</p>
                @endif
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Envío al cliente</p>
                <p class="text-sm text-slate-900">{{ $setting->notify_customer ? 'Sí' : 'No' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Estado</p>
                <p class="text-sm font-medium {{ $setting->active ? 'text-emerald-700' : 'text-slate-500' }}">
                    {{ $setting->active ? 'Activa' : 'Inactiva' }}
                </p>
            </div>
        </div>

        @if($missing !== [])
        <div class="border-t border-slate-100 bg-amber-50/60 px-5 py-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-800">Pendiente por completar</p>
            <ul class="mt-1.5 list-inside list-disc space-y-0.5 text-xs text-amber-800">
                @foreach($missing as $item)
                <li>{{ $item }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </section>
    @empty
    <section class="rounded-2xl border border-slate-200/90 bg-white p-10 text-center shadow-sm ring-1 ring-slate-900/[0.035]">
        <p class="text-sm text-slate-500">Todavía no hay negocios configurados para facturar electrónicamente.</p>
        @if($can_create)
        <button type="button" wire:click="openCreate" class="btn btn-primary btn-sm mt-4">Configurar el primero</button>
        @endif
    </section>
    @endforelse

    @if($showModal)
    <x-ui.modal centered maxWidth="2xl">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeModal"></div>
        </x-slot:backdrop>

        <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
            <h3 class="text-base font-semibold text-slate-900">
                {{ $form->isEditing() ? 'Editar configuración' : 'Nueva configuración' }}
            </h3>
            <button type="button" wire:click="closeModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form wire:submit="save" class="flex min-h-0 flex-1 flex-col">
            <div class="flex-1 space-y-5 overflow-y-auto px-4 py-5 sm:px-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @if($is_super_admin)
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Negocio <span class="text-rose-500">*</span></label>
                        <select wire:model="form.business_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.business_id') border-rose-400 bg-rose-50 @enderror">
                            <option value="">Seleccionar negocio</option>
                            @foreach($businesses as $business)
                            <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </select>
                        @error('form.business_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    @endif

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Entorno <span class="text-rose-500">*</span></label>
                        <select wire:model="form.environment" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                            <option value="test">Pruebas (habilitación)</option>
                            <option value="production">Producción</option>
                        </select>
                        @error('form.environment') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Formato del documento <span class="text-rose-500">*</span></label>
                        <select wire:model="form.document_format" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm">
                            <option value="json">JSON (JSON_DATASET)</option>
                            <option value="xml">XML (DATASET_DATASET)</option>
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Debe coincidir con el formato de entrada configurado por el proveedor en el perfil.</p>
                        @error('form.document_format') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-slate-700">Perfil de emisión (tr_tipo_id)</label>
                        <input type="number" wire:model="form.tr_tipo_id" placeholder="Lo entrega el proveedor"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm @error('form.tr_tipo_id') border-rose-400 bg-rose-50 @enderror">
                        @error('form.tr_tipo_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Resolución DIAN</p>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Número de resolución</label>
                            <input type="text" wire:model="form.resolution_number" placeholder="18760000001"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-mono @error('form.resolution_number') border-rose-400 @enderror">
                            @error('form.resolution_number') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Prefijo</label>
                            <input type="text" wire:model="form.prefix" placeholder="SETP"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-mono uppercase @error('form.prefix') border-rose-400 @enderror">
                            @error('form.prefix') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Prefijo de notas credito</label>
                            <input type="text" wire:model="form.credit_note_prefix" placeholder="NC"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-mono uppercase @error('form.credit_note_prefix') border-rose-400 @enderror">
                            @error('form.credit_note_prefix') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-slate-500">Las notas credito llevan su propia numeracion, aparte de la de las facturas.</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Rango inicial</label>
                            <input type="number" wire:model="form.range_from"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm @error('form.range_from') border-rose-400 @enderror">
                            @error('form.range_from') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Rango final</label>
                            <input type="number" wire:model="form.range_to"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm @error('form.range_to') border-rose-400 @enderror">
                            @error('form.range_to') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Vigente desde</label>
                            <input type="date" wire:model="form.valid_from"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm">
                            @error('form.valid_from') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Vigente hasta</label>
                            <input type="date" wire:model="form.valid_to"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm">
                            @error('form.valid_to') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Clave técnica</label>
                            <input type="text" wire:model="form.technical_key"
                                placeholder="{{ $form->isEditing() ? 'Déjala vacía para conservar la actual' : 'Clave técnica de la resolución' }}"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-mono @error('form.technical_key') border-rose-400 @enderror">
                            <p class="mt-1 text-xs text-slate-500">Se almacena cifrada. En pruebas puede dejarse vacía.</p>
                            @error('form.technical_key') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Próximo consecutivo</label>
                            <input type="number" wire:model="form.next_consecutive" placeholder="Por defecto, el rango inicial"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm @error('form.next_consecutive') border-rose-400 @enderror">
                            @error('form.next_consecutive') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">Identificador del software</label>
                            <input type="text" wire:model="form.software_id" placeholder="Lo entrega el portal de la DIAN"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-mono">
                            @error('form.software_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">PIN del software</label>
                            <input type="text" wire:model="form.software_pin"
                                placeholder="{{ $form->isEditing() ? 'Déjalo vacío para conservar el actual' : 'PIN del portal de la DIAN' }}"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-mono">
                            <p class="mt-1 text-xs text-slate-500">Se almacena cifrado.</p>
                            @error('form.software_pin') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Entrega al cliente</p>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" wire:model="form.notify_customer" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Enviar la factura por correo
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" wire:model="form.include_pdf" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Adjuntar PDF
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" wire:model="form.include_xml" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Adjuntar XML
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" wire:model="form.include_attachments" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Adjuntar anexos
                        </label>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Notas internas</label>
                    <textarea wire:model="form.notes" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm"></textarea>
                    @error('form.notes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-3 text-sm text-slate-700">
                    <span class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center">
                        <input type="checkbox" wire:model="form.active" class="peer sr-only">
                        <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-indigo-600"></span>
                        <span class="absolute left-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                    </span>
                    <span>Habilitar la emisión electrónica para este negocio</span>
                </label>
            </div>

            <div class="flex shrink-0 justify-end gap-2 border-t border-slate-100 px-4 py-4 sm:px-6">
                <button type="button" wire:click="closeModal" class="btn btn-outline-secondary btn-sm">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="btn btn-primary btn-sm">
                    <span wire:loading.remove wire:target="save">Guardar</span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </form>
    </x-ui.modal>
    @endif
</div>
