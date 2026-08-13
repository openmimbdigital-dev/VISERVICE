<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6"
    x-data="{
        copiedRegister: false,
        copiedPortal: false,
        copy(url, type) {
            navigator.clipboard.writeText(url);
            if (type === 'register') {
                this.copiedRegister = true;
                setTimeout(() => this.copiedRegister = false, 2000);
            } else {
                this.copiedPortal = true;
                setTimeout(() => this.copiedPortal = false, 2000);
            }
        },
        downloadQr(svg, filename) {
            const blob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            link.click();
            URL.revokeObjectURL(url);
        }
    }">
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <span class="rounded bg-slate-200/50 px-1.5 py-0.5">{{ org_term('Negocios') }}</span>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.participants.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Participantes</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Enlace público</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">{{ org_term('Negocios') }}</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Enlaces públicos</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    Comparte estos enlaces o códigos QR de <strong>{{ $business_name }}</strong>.
                    El identificador del negocio va cifrado en la URL.
                </p>
            </div>
            <a href="{{ route('admin.participants.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">Volver</a>
        </div>
    </header>

    {{-- Portal Participantes --}}
    <section class="mb-6 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Portal público — Participantes</h2>
            <p class="mt-1 text-xs text-slate-500">Acceso con PIN de 6 dígitos y documento del participante. Bienvenida e ítems según el tipo de organización.</p>
        </div>
        <div class="grid grid-cols-1 gap-4 p-5 sm:p-6 lg:grid-cols-2">
            <div class="space-y-4">
                @can('participants.edit')
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-4">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="text-sm font-semibold text-slate-800">PIN de acceso al portal</h3>
                        @if($portal_pin_configured)
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20">Configurado</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-amber-600/20">Sin configurar</span>
                        @endif
                    </div>
                    <p class="mb-4 text-xs text-slate-600">
                        Los participantes deberán ingresar este PIN junto con su tipo y número de documento para acceder al portal.
                    </p>
                    <form wire:submit="savePortalPin" class="space-y-3" x-data="{ showSaved: false, showPin: false, showConfirm: false }">
                        @if($portal_pin_configured)
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-slate-700">PIN guardado</label>
                                @if(filled($portal_pin_saved))
                                    <div class="relative">
                                        <input type="password"
                                            readonly
                                            value="{{ $portal_pin_saved }}"
                                            x-bind:type="showSaved ? 'text' : 'password'"
                                            class="w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 pr-11 text-sm tracking-[0.3em] text-slate-700">
                                        <button type="button"
                                            @click="showSaved = ! showSaved"
                                            x-bind:aria-label="showSaved ? 'Ocultar PIN' : 'Mostrar PIN'"
                                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 transition hover:text-slate-600">
                                            <svg x-show="! showSaved" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <svg x-show="showSaved" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <p class="rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2.5 text-xs text-amber-800">
                                        El PIN está configurado pero no se puede mostrar. Guárdalo de nuevo para visualizarlo aquí.
                                    </p>
                                @endif
                            </div>
                        @endif
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-slate-700">
                                    {{ $portal_pin_configured ? 'Nuevo PIN (6 dígitos)' : 'PIN (6 dígitos)' }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password"
                                        inputmode="numeric"
                                        maxlength="6"
                                        wire:model="portal_pin"
                                        placeholder="000000"
                                        x-bind:type="showPin ? 'text' : 'password'"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 pr-11 text-sm tracking-[0.3em] transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('portal_pin') border-rose-400 bg-rose-50 @enderror">
                                    <button type="button"
                                        @click="showPin = ! showPin"
                                        x-bind:aria-label="showPin ? 'Ocultar PIN' : 'Mostrar PIN'"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 transition hover:text-slate-600">
                                        <svg x-show="! showPin" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showPin" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('portal_pin') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-slate-700">Confirmar PIN <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <input type="password"
                                        inputmode="numeric"
                                        maxlength="6"
                                        wire:model="portal_pin_confirmation"
                                        placeholder="000000"
                                        x-bind:type="showConfirm ? 'text' : 'password'"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 pr-11 text-sm tracking-[0.3em] transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 @error('portal_pin_confirmation') border-rose-400 bg-rose-50 @enderror">
                                    <button type="button"
                                        @click="showConfirm = ! showConfirm"
                                        x-bind:aria-label="showConfirm ? 'Ocultar confirmación' : 'Mostrar confirmación'"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 transition hover:text-slate-600">
                                        <svg x-show="! showConfirm" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showConfirm" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                                @error('portal_pin_confirmation') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <button type="submit"
                            wire:loading.attr="disabled"
                            class="btn btn-primary btn-sm w-full justify-center sm:w-auto">
                            {{ $portal_pin_configured ? 'Actualizar PIN' : 'Guardar PIN' }}
                        </button>
                    </form>
                </div>
                @else
                <p class="text-sm text-slate-600">
                    @if($portal_pin_configured)
                        El PIN de acceso al portal está configurado. Contacta a un administrador si necesitas cambiarlo.
                    @else
                        El portal requiere un PIN de acceso que aún no ha sido configurado.
                    @endif
                </p>
                @endcan
                <p class="text-sm text-slate-600">
                    Quien abra este enlace deberá autenticarse con PIN y documento. Los módulos visibles dependen de la configuración por tipo de organización.
                </p>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-stretch">
                    <input type="text" readonly value="{{ $portal_url }}"
                        class="form-input w-full flex-1 border px-3 py-2 font-mono text-xs text-slate-700 sm:text-sm" />
                    <button type="button" @click="copy(@js($portal_url), 'portal')"
                        class="btn btn-primary w-full justify-center sm:w-auto sm:shrink-0">
                        <span x-show="!copiedPortal">Copiar enlace</span>
                        <span x-show="copiedPortal" x-cloak>Copiado</span>
                    </button>
                </div>
                <a href="{{ $portal_url }}" target="_blank" rel="noopener noreferrer"
                    class="inline-flex text-sm font-medium text-indigo-600 hover:text-indigo-700">
                    Abrir portal público
                </a>
            </div>
            <div class="flex flex-col items-center gap-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="h-[220px] w-[220px] text-slate-900 [&>svg]:h-full [&>svg]:w-full">
                        {!! $portal_qr_svg !!}
                    </div>
                </div>
                <button type="button" @click="downloadQr(@js($portal_qr_svg), 'portal-participantes-qr.svg')"
                    class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">
                    Descargar QR del portal
                </button>
            </div>
        </div>
    </section>

    {{-- Registro de participante --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.035]">
        <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <h2 class="font-semibold text-slate-800">Registro público de participante</h2>
            <p class="mt-1 text-xs text-slate-500">Formulario para crear un participante sin iniciar sesión.</p>
        </div>
        <div class="grid grid-cols-1 gap-4 p-5 sm:p-6 lg:grid-cols-2">
            <div class="space-y-4">
                <p class="text-sm text-slate-600">
                    Quien abra este enlace podrá registrarse como participante. No compartas la URL si no deseas registros abiertos.
                </p>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-stretch">
                    <input type="text" readonly value="{{ $public_url }}"
                        class="form-input w-full flex-1 border px-3 py-2 font-mono text-xs text-slate-700 sm:text-sm" />
                    <button type="button" @click="copy(@js($public_url), 'register')"
                        class="btn btn-primary w-full justify-center sm:w-auto sm:shrink-0">
                        <span x-show="!copiedRegister">Copiar enlace</span>
                        <span x-show="copiedRegister" x-cloak>Copiado</span>
                    </button>
                </div>
                <a href="{{ $public_url }}" target="_blank" rel="noopener noreferrer"
                    class="inline-flex text-sm font-medium text-indigo-600 hover:text-indigo-700">
                    Abrir formulario de registro
                </a>
            </div>
            <div class="flex flex-col items-center gap-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="h-[220px] w-[220px] text-slate-900 [&>svg]:h-full [&>svg]:w-full">
                        {!! $qr_svg !!}
                    </div>
                </div>
                <button type="button" @click="downloadQr(@js($qr_svg), 'registro-participantes-qr.svg')"
                    class="btn btn-outline-secondary btn-sm w-full justify-center sm:w-auto">
                    Descargar QR de registro
                </button>
            </div>
        </div>
    </section>
</div>
