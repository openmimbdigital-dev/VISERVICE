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
            <p class="mt-1 text-xs text-slate-500">Bienvenida e ítems públicos (p. ej. Eventos) según el tipo de organización.</p>
        </div>
        <div class="grid grid-cols-1 gap-4 p-5 sm:p-6 lg:grid-cols-2">
            <div class="space-y-4">
                <p class="text-sm text-slate-600">
                    Quien abra este enlace verá la sección Participantes sin iniciar sesión. Los módulos visibles dependen de la configuración por tipo de organización.
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
