<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SouulBi' }} — SouulBi</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-initial.jpeg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        .grid-dot {
            background-image: radial-gradient(rgba(255,255,255,.12) 1px, transparent 1px);
            background-size: 28px 28px;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/css/utils.css', 'resources/css/index.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-white text-slate-900 antialiased">
    <div class="flex min-h-screen">
        {{-- Panel branding (mismo lenguaje visual que auth/registro) --}}
        <aside class="relative hidden w-2/5 flex-col overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 p-10 lg:flex xl:w-1/3">
            <div class="absolute inset-0 grid-dot opacity-60"></div>
            <div class="absolute -left-40 -top-40 h-96 w-96 rounded-full bg-indigo-600/20 blur-3xl"></div>
            <div class="absolute -bottom-32 -right-20 h-80 w-80 rounded-full bg-indigo-700/25 blur-3xl"></div>

            <div class="relative z-10 mb-12 flex items-center gap-3">
                <img src="{{ asset('images/logo-initial.jpeg') }}" alt="SouulBi" class="h-10 w-auto select-none drop-shadow-xl">
                <span class="text-2xl font-extrabold tracking-tight text-white">Souul<span class="text-indigo-400">Bi</span></span>
            </div>

            <div class="relative z-10 flex-1">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-300/90">Registro público</p>
                <h1 class="mt-3 text-2xl font-bold tracking-tight text-white">Participantes</h1>
                <p class="mt-3 max-w-sm text-sm leading-relaxed text-slate-400">
                    Completa tus datos para unirte
                    @isset($business_name)
                        a <span class="font-medium text-indigo-200">{{ $business_name }}</span>
                    @else
                        al directorio
                    @endisset.
                </p>
            </div>

            <p class="relative z-10 mt-8 text-xs text-slate-600">© {{ date('Y') }} SouulBi · Todos los derechos reservados</p>
        </aside>

        {{-- Contenido del formulario --}}
        <main class="flex flex-1 items-start justify-center overflow-y-auto bg-slate-100 p-4 sm:p-8 lg:items-center lg:p-10">
            <div class="w-full max-w-2xl">
                {{-- Cabecera móvil --}}
                <div class="mb-6 flex items-center gap-2 lg:hidden">
                    <img src="{{ asset('images/logo-initial.jpeg') }}" alt="SouulBi" class="h-8 w-auto">
                    <span class="text-base font-bold text-slate-800">SouulBi</span>
                    @isset($business_name)
                        <span class="text-slate-300">·</span>
                        <span class="truncate text-sm text-slate-500">{{ $business_name }}</span>
                    @endisset
                </div>

                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal', (payload) => {
                const data = Array.isArray(payload) ? payload[0] : payload;
                if (window.Swal && data) {
                    window.Swal.fire(data);
                }
            });
        });
    </script>
</body>
</html>
