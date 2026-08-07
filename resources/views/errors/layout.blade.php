<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — SouulBi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .grid-dot {
            background-image: radial-gradient(rgba(255,255,255,.08) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-8px); }
        }
        .float { animation: float 4s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 flex items-center justify-center p-6">

    <div class="absolute inset-0 grid-dot pointer-events-none"></div>
    <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-indigo-600/15 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-20 w-80 h-80 rounded-full bg-primary-700/20 blur-3xl pointer-events-none"></div>

    <div class="relative z-10 w-full max-w-lg text-center">

        {{-- Logo --}}
        <div class="flex justify-center mb-8">
            <img src="{{ asset('images/logo-initial.jpeg') }}" alt="SouulBi" class="h-14 w-auto drop-shadow-xl float">
        </div>

        {{-- Código de error (decorativo, grande y semitransparente) --}}
        <div class="mb-2 leading-none select-none">
            <span class="text-8xl font-extrabold text-white/10">@yield('code')</span>
        </div>

        {{-- Ícono central --}}
        <div class="flex justify-center -mt-6 mb-6">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-sm border border-white/10 shadow-xl">
                @yield('icon')
            </div>
        </div>

        {{-- Título --}}
        <h1 class="text-2xl font-bold text-white mb-3">@yield('title')</h1>

        {{-- Descripción --}}
        <p class="text-slate-400 text-sm leading-relaxed max-w-sm mx-auto mb-8">@yield('description')</p>

        {{-- Acciones --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            @auth
                <a href="{{ url('/dashboard') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-95 transition-all">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    Ir al inicio
                </a>
            @else
                <a href="{{ url('/') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-95 transition-all">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Iniciar sesión
                </a>
            @endauth

            <button onclick="history.back()"
                class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/5 px-5 py-2.5 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white active:scale-95 transition-all">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver atrás
            </button>
        </div>

        <p class="mt-12 text-xs text-slate-600">© {{ date('Y') }} SouulBi · Si el problema persiste contacta al administrador.</p>
    </div>

</body>
</html>
