<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activación pendiente — VISERVICE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .pulse-slow { animation: pulse-slow 2.5s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 flex items-center justify-center p-6">

    <div class="w-full max-w-md text-center">

        {{-- Ícono animado --}}
        <div class="flex justify-center mb-8">
            <div class="relative">
                <div class="w-24 h-24 rounded-full bg-amber-500/20 flex items-center justify-center pulse-slow">
                    <div class="w-16 h-16 rounded-full bg-amber-500/30 flex items-center justify-center">
                        <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Logo --}}
        <div class="flex items-center justify-center gap-2 mb-6">
            <img src="{{ asset('images/logo-initial.png') }}" alt="VISERVICE" class="h-8 w-auto">
            <span class="text-xl font-extrabold text-white">VIS<span class="text-indigo-400">ERVICE</span></span>
        </div>

        {{-- Mensaje principal --}}
        <h1 class="text-3xl font-bold text-white mb-3">¡Registro exitoso!</h1>
        <p class="text-slate-300 text-lg mb-2">Tu pago está siendo verificado.</p>
        <p class="text-slate-400 text-sm leading-relaxed">
            Hemos recibido tu solicitud de pago. Nuestro equipo la revisará a la brevedad
            y recibirás confirmación una vez tu cuenta esté activa.
        </p>

        {{-- Info del usuario --}}
        @auth
        <div class="mt-8 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-sm px-6 py-5 text-left space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-sm shrink-0">
                    {{ strtoupper(substr(auth()->user()->first_name ?? auth()->user()->username, 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-white">
                        {{ trim((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')) ?: auth()->user()->username }}
                    </p>
                    <p class="text-xs text-slate-400">{{ auth()->user()->email }}</p>
                </div>
            </div>
            @if(auth()->user()->business)
            <div class="border-t border-white/10 pt-3 flex items-center gap-2 text-sm text-slate-300">
                <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                {{ auth()->user()->business->name }}
            </div>
            @endif
        </div>
        @endauth

        {{-- Pasos de lo que pasa --}}
        <div class="mt-6 rounded-2xl border border-white/10 bg-white/5 px-6 py-5">
            <p class="text-sm font-semibold text-slate-300 text-left mb-4">¿Qué pasa ahora?</p>
            <div class="space-y-3">
                @foreach([
                    ['Verificamos tu pago', 'Nuestro equipo revisa la información enviada.', true],
                    ['Te confirmamos', 'Recibirás un aviso cuando tu cuenta esté activa.', false],
                    ['Acceso completo', 'Podrás usar todas las funciones de VISERVICE.', false],
                ] as [$title, $desc, $active])
                <div class="flex items-start gap-3">
                    <div @class(['w-5 h-5 rounded-full flex items-center justify-center shrink-0 mt-0.5', 'bg-amber-500' => $active, 'bg-white/10' => !$active])>
                        @if($active)
                            <div class="w-2 h-2 rounded-full bg-white pulse-slow"></div>
                        @else
                            <div class="w-1.5 h-1.5 rounded-full bg-white/30"></div>
                        @endif
                    </div>
                    <div class="text-left">
                        <p @class(['text-sm font-medium', 'text-white' => $active, 'text-slate-400' => !$active])>{{ $title }}</p>
                        <p class="text-xs text-slate-500">{{ $desc }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Acciones --}}
        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl border border-white/20 px-5 py-2.5 text-sm font-medium text-slate-300 hover:bg-white/10 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Cerrar sesión
                </button>
            </form>
        </div>

        <p class="mt-10 text-xs text-slate-600">© {{ date('Y') }} VISERVICE · Sistema de gestión de talleres</p>
    </div>

</body>
</html>
