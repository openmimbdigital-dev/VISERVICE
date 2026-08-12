<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Participantes' }} — SouulBi</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-initial.jpeg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    @vite(['resources/css/app.css', 'resources/css/utils.css', 'resources/css/index.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <div
        class="min-h-screen"
        x-data="{
            mobileMenuOpen: false,
            openMenu() { this.mobileMenuOpen = true },
            closeMenu() { this.mobileMenuOpen = false },
            toggleMenu() { this.mobileMenuOpen = ! this.mobileMenuOpen },
        }"
        @keydown.escape.window="closeMenu()"
    >
        {{-- Header móvil --}}
        <header class="sticky top-0 z-40 flex items-center justify-between gap-3 border-b border-slate-800 bg-slate-900 px-4 py-3 text-slate-100 lg:hidden">
            <div class="flex min-w-0 items-center gap-3">
                <img src="{{ asset('images/logo-initial.jpeg') }}" alt="SouulBi" class="h-8 w-auto shrink-0">
                <div class="min-w-0">
                    <p class="text-sm font-bold tracking-tight text-white">Souul<span class="text-indigo-400">Bi</span></p>
                    @isset($business_name)
                        <p class="truncate text-xs text-slate-400">{{ $business_name }}</p>
                    @endisset
                </div>
            </div>
            <button type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-200 transition hover:bg-slate-800"
                @click.stop="toggleMenu()"
                :aria-expanded="mobileMenuOpen.toString()"
                aria-label="Abrir menú">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </header>

        {{-- Overlay móvil --}}
        <div
            x-show="mobileMenuOpen"
            x-cloak
            x-transition.opacity.duration.200ms
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden"
            @click="closeMenu()"
            aria-hidden="true"></div>

        <div class="flex min-h-[calc(100vh-3.5rem)] lg:min-h-screen">
            {{-- Sidebar: drawer en móvil, fijo en desktop --}}
            <aside
                class="fixed inset-y-0 left-0 z-50 flex w-[min(100%,18rem)] -translate-x-full flex-col border-r border-slate-800 bg-slate-900 text-slate-100 transition-transform duration-200 ease-out lg:static lg:z-auto lg:w-64 lg:shrink-0 lg:translate-x-0"
                :class="mobileMenuOpen && 'translate-x-0'"
                @click.stop>

                <div class="flex items-center justify-between gap-3 px-5 py-5">
                    <div class="flex min-w-0 items-center gap-3">
                        <img src="{{ asset('images/logo-initial.jpeg') }}" alt="SouulBi" class="hidden h-9 w-auto lg:block">
                        <div class="min-w-0">
                            <p class="text-sm font-bold tracking-tight text-white">Souul<span class="text-indigo-400">Bi</span></p>
                            @isset($business_name)
                                <p class="truncate text-xs text-slate-400">{{ $business_name }}</p>
                            @endisset
                        </div>
                    </div>
                    <button type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-300 transition hover:bg-slate-800 lg:hidden"
                        @click="closeMenu()"
                        aria-label="Cerrar menú">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 overflow-y-auto px-3 pb-5">
                    <p class="mb-2 px-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-300/90">Participantes</p>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ $home_url ?? '#' }}"
                                wire:navigate
                                @click="closeMenu()"
                                @class([
                                    'flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                                    'bg-indigo-600 text-white' => ($active_nav ?? '') === 'home',
                                    'text-slate-300 hover:bg-slate-800 hover:text-white' => ($active_nav ?? '') !== 'home',
                                ])>
                                Inicio
                            </a>
                        </li>
                        @foreach(($portal_items ?? []) as $item)
                            @if(! empty($item['url']))
                                <li>
                                    <a href="{{ $item['url'] }}"
                                        wire:navigate
                                        @click="closeMenu()"
                                        @class([
                                            'flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                                            'bg-indigo-600 text-white' => ($active_nav ?? '') === ($item['key'] ?? ''),
                                            'text-slate-300 hover:bg-slate-800 hover:text-white' => ($active_nav ?? '') !== ($item['key'] ?? ''),
                                        ])>
                                        {{ $item['label'] }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </nav>
            </aside>

            <main class="min-w-0 flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <div @class(['mx-auto w-full', $content_max_width ?? 'max-w-4xl'])>
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
