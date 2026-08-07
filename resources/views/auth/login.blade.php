<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — SouulBi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .grid-dot {
            background-image: radial-gradient(rgba(255,255,255,.12) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-10px); }
        }
        .float { animation: float 5s ease-in-out infinite; }
        @keyframes pulse-ring {
            0%   { transform: scale(.95); box-shadow: 0 0 0 0 rgba(99,102,241,.5); }
            70%  { transform: scale(1);   box-shadow: 0 0 0 12px rgba(99,102,241,0); }
            100% { transform: scale(.95); box-shadow: 0 0 0 0 rgba(99,102,241,0); }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-950 flex">

    {{-- Panel izquierdo — branding --}}
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 flex-col items-center justify-center p-12">
        {{-- Fondo decorativo --}}
        <div class="absolute inset-0 grid-dot opacity-60"></div>
        <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-indigo-600/20 blur-3xl"></div>
        <div class="absolute -bottom-32 -right-20 w-80 h-80 rounded-full bg-primary-700/25 blur-3xl"></div>

        {{-- Logo y nombre --}}
        <div class="relative z-10 flex flex-col items-center text-center max-w-md">
            <div class="float">
                <img src="{{ asset('images/logo-initial.jpeg') }}"
                     alt="SouulBi"
                     class="h-28 w-auto drop-shadow-2xl mb-6 select-none">
            </div>
            <h1 class="text-4xl xl:text-5xl font-extrabold text-white tracking-tight leading-tight">
                Souul<span class="text-indigo-400">Bi</span>
            </h1>
            <p class="mt-4 text-slate-300 text-lg font-light leading-relaxed">
                Gestión . Control . Impacto
            </p>

            <div class="mt-10 grid grid-cols-3 gap-4 w-full">
                @foreach([['🔧','Taller','Clientes, vehículos & OTs'],['📋','Catálogo','Servicios & repuestos'],['📊','Control','Cotizaciones & facturas']] as [$icon,$title,$desc])
                <div class="rounded-2xl border border-white/10 bg-white/5 backdrop-blur-sm p-4 text-left hover:bg-white/10 transition">
                    <span class="text-2xl">{{ $icon }}</span>
                    <p class="mt-2 text-sm font-semibold text-white">{{ $title }}</p>
                    <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Footer izquierdo --}}
        <p class="absolute bottom-6 text-xs text-slate-600 z-10">© {{ date('Y') }} SouulBi · Todos los derechos reservados</p>
    </div>

    {{-- Panel derecho — formulario --}}
    <div class="w-full lg:w-1/2 xl:w-2/5 flex items-center justify-center p-6 sm:p-10 bg-white">
        <div class="w-full max-w-sm">

            {{-- Logo mobile --}}
            <div class="flex flex-col items-center mb-8 lg:hidden">
                <img src="{{ asset('images/logo-initial.jpeg') }}" alt="SouulBi" class="h-16 w-auto mb-3">
                <h2 class="text-2xl font-bold text-slate-900">SouulBi</h2>
            </div>

            <div class="mb-8">
                <h2 class="text-2xl font-bold text-slate-900">Bienvenido de vuelta</h2>
                <p class="mt-1 text-sm text-slate-500">Ingresa tus credenciales para continuar.</p>
            </div>

            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
                @csrf

                {{-- Usuario --}}
                <div>
                    <label for="username" class="block text-sm font-medium text-slate-700 mb-1.5">Usuario</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="h-4.5 w-4.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input
                            id="username" name="username" type="text"
                            autocomplete="username" required
                            value="{{ old('username') }}"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('username') border-red-400 bg-red-50 @enderror"
                            placeholder="Tu nombre de usuario"
                        >
                    </div>
                    @error('username')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                            <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Contraseña</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="h-4.5 w-4.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input
                            id="password" name="password" type="password"
                            autocomplete="current-password" required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-10 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition @error('password') border-red-400 bg-red-50 @enderror"
                            placeholder="Tu contraseña"
                        >
                        <button type="button" id="password-toggle" aria-label="Mostrar contraseña"
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 transition">
                            <svg id="password-icon-show" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="password-icon-hide" class="hidden h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Recordar --}}
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    <label for="remember" class="ml-2 text-sm text-slate-600 cursor-pointer">Recordar sesión</label>
                </div>

                {{-- Botón --}}
                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-150">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Iniciar sesión
                </button>
            </form>

            {{-- Alertas --}}
            @if (session('success'))
                <div class="mt-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any() && !$errors->has('username') && !$errors->has('password'))
                <div class="mt-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <strong class="font-semibold">Error de autenticación:</strong>
                        <ul class="mt-1 list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="mt-6 text-center">
                <p class="text-sm text-slate-500">
                    ¿No tienes cuenta?
                    <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 transition">
                        Registra tu comercio
                    </a>
                </p>
            </div>

            <p class="mt-4 text-center text-xs text-slate-400">
                © {{ date('Y') }} SouulBi · Sistema de gestión de talleres
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const username = document.getElementById('username');
            if (username) username.focus();

            const password = document.getElementById('password');
            const toggle = document.getElementById('password-toggle');
            const iconShow = document.getElementById('password-icon-show');
            const iconHide = document.getElementById('password-icon-hide');

            if (!password || !toggle || !iconShow || !iconHide) return;

            toggle.addEventListener('click', function () {
                const visible = password.type === 'text';
                password.type = visible ? 'password' : 'text';
                iconShow.classList.toggle('hidden', !visible);
                iconHide.classList.toggle('hidden', visible);
                toggle.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
            });
        });
    </script>
</body>
</html>
