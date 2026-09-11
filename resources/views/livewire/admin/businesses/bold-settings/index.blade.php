<div class="relative mx-auto w-full max-w-[90rem]">
    {{-- Breadcrumb --}}
    <nav class="mb-6 flex items-center gap-x-2 text-xs text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="transition hover:text-indigo-600">Inicio</a>
        <span>/</span>
        <a href="{{ route('admin.businesses.index') }}" wire:navigate class="transition hover:text-indigo-600">Negocios</a>
        <span>/</span>
        <span class="font-medium text-slate-700">Pasarela de pagos</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-stretch lg:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Negocios</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Pasarela de pagos</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">
                    Las llaves de Bold con las que cada negocio cobra sus facturas. El dinero entra a la cuenta
                    del negocio; sin llaves propias entra a la de la plataforma y hay que girárselo después.
                </p>
            </div>
        </div>
    </header>

    @if(! $platform_ready)
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
        <p class="text-sm font-semibold text-amber-900">La plataforma tampoco tiene llaves configuradas</p>
        <p class="mt-1 text-xs text-amber-800">
            Sin <span class="font-mono">IDENTITY_KEY_BOLD</span> en el entorno, los negocios sin llave propia no podrán cobrar en línea.
        </p>
    </div>
    @endif

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Negocios</h2>
            <span class="text-xs text-slate-500">{{ $businesses->count() }} activo(s)</span>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($businesses as $business)
            @php($setting = $settings->get($business->id))
            @php($usable = $setting?->isUsable() ?? false)
            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="font-medium text-slate-900">{{ $business->name }}</p>
                    <p class="mt-0.5 text-xs {{ $usable ? 'text-emerald-700' : 'text-amber-700' }}">
                        @if($usable)
                            Cobra a su propia cuenta de Bold
                            @if($setting && ! $setting->active) · <span class="text-slate-500">desactivada</span> @endif
                        @else
                            Sin llaves propias — sus cobros entran a la cuenta de la plataforma
                        @endif
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $usable ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20' }}">
                        {{ $usable ? 'Cuenta propia' : 'Respaldo' }}
                    </span>

                    @if($usable)
                    <button type="button" wire:click="testConnection({{ $business->id }})"
                        wire:loading.attr="disabled" wire:target="testConnection"
                        class="btn btn-outline-secondary btn-sm">
                        <span wire:loading.remove wire:target="testConnection">Probar llave</span>
                        <span wire:loading wire:target="testConnection">Probando...</span>
                    </button>
                    @endif

                    @if($can_edit)
                    <button type="button" wire:click="openEdit({{ $business->id }})" class="btn btn-primary btn-sm">
                        {{ $setting ? 'Editar llaves' : 'Configurar' }}
                    </button>
                    @endif
                </div>
            </div>
            @empty
            <p class="px-5 py-8 text-center text-sm text-slate-500">No hay negocios activos.</p>
            @endforelse
        </div>
    </section>

    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3">
        <p class="text-xs font-semibold text-slate-700">Cada negocio debe registrar esta URL en su panel de Bold</p>
        <p class="mt-1 font-mono text-xs text-slate-600">{{ $webhook_url }}</p>
        <p class="mt-1 text-xs text-slate-500">
            Sin eso, sus pagos solo se acreditan cuando alguien consulta desde la factura.
        </p>
    </div>

    @if($showModal)
    <x-ui.modal centered maxWidth="md">
        <x-slot:backdrop>
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeModal"></div>
        </x-slot:backdrop>

        <form wire:submit="save" class="flex min-h-0 flex-col">
            <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-6">
                <h3 class="text-base font-semibold text-slate-900">Llaves de Bold</h3>
                <button type="button" wire:click="closeModal" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 space-y-4 overflow-y-auto px-4 py-5 sm:px-6">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Llave de identidad</label>
                    <input type="text" wire:model="identity_key" autocomplete="off" placeholder="Dejar vacío para conservar la guardada"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-mono text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('identity_key') border-rose-400 bg-rose-50 @enderror">
                    @error('identity_key') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-slate-500">Autentica la creación de links. Es la que decide a qué cuenta entra el dinero.</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Llave secreta</label>
                    <input type="text" wire:model="secret_key" autocomplete="off" placeholder="Dejar vacío para conservar la guardada"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 font-mono text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('secret_key') border-rose-400 bg-rose-50 @enderror">
                    @error('secret_key') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-slate-500">Verifica la firma de los avisos de pago. Sin ella, sus pagos no se acreditan solos.</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-700">Estado</label>
                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="$toggle('active')"
                            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors duration-200 {{ $active ? 'bg-indigo-600' : 'bg-slate-300' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200 {{ $active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                        <span class="text-sm {{ $active ? 'font-medium text-emerald-700' : 'text-slate-500' }}">
                            {{ $active ? 'Cobra a su cuenta' : 'Desactivada — vuelve al respaldo de la plataforma' }}
                        </span>
                    </div>
                </div>

                <p class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-xs text-slate-500">
                    Las llaves se guardan cifradas y no se vuelven a mostrar. Para cambiarlas, pega las nuevas.
                </p>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-100 px-4 py-4 sm:flex-row sm:justify-end sm:gap-3 sm:px-6">
                <button type="button" wire:click="closeModal" class="btn btn-outline-secondary btn-sm">Cancelar</button>
                <button type="submit" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                    <span wire:loading.remove wire:target="save">Guardar</span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            </div>
        </form>
    </x-ui.modal>
    @endif
</div>
