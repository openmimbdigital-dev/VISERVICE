<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'VISERVICE' }} — VISERVICE</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-initial.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;600;700&display=swap">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
    </style>
    @vite(['resources/css/app.css', 'resources/css/utils.css', 'resources/css/index.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="bg-slate-100 text-slate-900 antialiased min-h-screen">
    @php
        $u = $user ?? auth()->user();
        $displayName = ($u?->full_name ?: null) ?? $u?->username ?? $u?->email ?? 'Usuario';
        $displayEmail = $u?->email ?? '';
        $initials = strtoupper(mb_substr(preg_replace('/\s+/', '', $displayName), 0, 2));
        if (mb_strlen($displayName) >= 2 && preg_match('/\s/u', $displayName)) {
            $parts = preg_split('/\s+/u', trim($displayName));
            $initials = mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[count($parts) - 1] ?? '', 0, 1));
        }
        $business       = $u?->business ?? null;
        $businessLogo   = ($business?->logo) ? \Illuminate\Support\Facades\Storage::disk('public')->url($business->logo) : null;
        $businessName   = $business?->name ?? null;
        $isComercio     = $u?->hasRole('Comercio') ?? false;
    @endphp

    <div
        class="min-h-screen flex"
        x-data="{
            sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
            mobileSidebarOpen: false,
            navOpen: JSON.parse(localStorage.getItem('sidebarNavOpen') || '{}'),
            collapsedOpen: null,
            toggleSidebar() {
                this.sidebarCollapsed = !this.sidebarCollapsed;
                localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
                this.collapsedOpen = null;
            },
            toggleMobileSidebar() {
                this.mobileSidebarOpen = !this.mobileSidebarOpen;
                if (this.mobileSidebarOpen) {
                    this.collapsedOpen = null;
                }
            },
            closeMobileSidebar() {
                this.mobileSidebarOpen = false;
            },
            toggleSidebarFromHeader() {
                if (window.matchMedia('(min-width: 1024px)').matches) {
                    this.toggleSidebar();
                } else {
                    this.toggleMobileSidebar();
                }
            },
            isNavOpen(slug) {
                if (this.navOpen[slug] === undefined) {
                    return true;
                }
                return !!this.navOpen[slug];
            },
            toggleNav(slug) {
                this.navOpen[slug] = !this.isNavOpen(slug);
                localStorage.setItem('sidebarNavOpen', JSON.stringify(this.navOpen));
            },
            toggleCollapsedMenu(slug) {
                this.collapsedOpen = this.collapsedOpen === slug ? null : slug;
            },
            closeCollapsedMenu() {
                this.collapsedOpen = null;
            },
            showSidebarLabels() {
                return !this.sidebarCollapsed || this.mobileSidebarOpen;
            },
            showSidebarIconsOnly() {
                return this.sidebarCollapsed && !this.mobileSidebarOpen;
            }
        }"
        x-init="@foreach($sidebarActiveSlugs ?? [] as $slug) navOpen['{{ $slug }}'] = true; @endforeach"
        @keydown.escape.window="closeMobileSidebar(); closeCollapsedMenu()"
    >
        {{-- Overlay móvil / tablet --}}
        <div
            x-cloak
            x-show="mobileSidebarOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="closeMobileSidebar()"
            class="fixed inset-0 z-30 bg-slate-900/60 backdrop-blur-sm lg:hidden"
        ></div>

        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-40 flex h-full flex-col border-r border-slate-800 bg-slate-900 text-slate-100 transition-[transform,width] duration-200 ease-out overflow-x-visible overflow-y-visible w-60 lg:relative lg:z-40 lg:h-auto lg:min-h-screen lg:shrink-0"
            :class="[
                mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                sidebarCollapsed ? 'lg:w-[4.25rem]' : 'lg:w-60',
            ]"
        >
            <div class="h-14 flex items-center px-3 border-b border-slate-800/80 gap-2">
                @if($isComercio && $businessLogo)
                    <img src="{{ $businessLogo }}"
                         alt="{{ $businessName }}"
                         class="h-8 w-8 shrink-0 rounded-lg object-cover ring-1 ring-white/10">
                @elseif($isComercio && $businessName)
                    <span class="h-8 w-8 shrink-0 flex items-center justify-center rounded-lg bg-violet-600 text-white text-xs font-bold ring-1 ring-white/10">
                        {{ strtoupper(substr($businessName, 0, 2)) }}
                    </span>
                @else
                    <img src="{{ asset('images/logo-initial.png') }}"
                         alt="VISERVICE"
                         class="h-8 w-8 shrink-0 rounded-lg object-contain bg-white/5 p-0.5">
                @endif
                <div class="min-w-0 flex-1" x-show="showSidebarLabels()" x-transition.opacity>
                    <p class="font-semibold text-white truncate text-sm">
                        {{ ($isComercio && $businessName) ? $businessName : 'VISERVICE' }}
                    </p>
                    <p class="text-[11px] text-slate-400 truncate">Panel de control</p>
                </div>
            </div>

            <nav class="flex-1 min-h-0 space-y-1 overflow-y-auto overflow-x-visible py-4 px-2" @click="if ($event.target.closest('a[href]')) closeMobileSidebar()">
                <a
                    href="{{ route('dashboard') }}"
                    wire:navigate
                    class="flex items-center gap-3 rounded-lg px-2.5 py-2.5 text-sm font-medium transition
                        {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                    title="Inicio"
                >
                    <svg class="h-5 w-5 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    <span class="truncate" x-show="showSidebarLabels()" x-transition.opacity>Inicio</span>
                </a>

                <x-layout.sidebar-nav :sections="$sidebarMenuSections ?? []" />

            </nav>

            <div class="hidden border-t border-slate-800/80 p-2 lg:block">
                <button
                    type="button"
                    @click="toggleSidebar()"
                    class="w-full flex items-center justify-center gap-2 rounded-lg py-2 text-slate-400 hover:bg-slate-800 hover:text-white transition text-sm"
                    :title="sidebarCollapsed ? 'Expandir menú' : 'Encoger menú'"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                    <span x-show="showSidebarLabels()" class="truncate">Encoger</span>
                </button>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex-1 flex flex-col min-w-0 min-h-screen">
            <header class="relative z-20 flex h-14 shrink-0 items-center justify-between border-b border-slate-200/80 bg-white/95 px-4 shadow-sm backdrop-blur-sm lg:px-6">

                <div class="flex min-w-0 flex-1 items-center gap-3">
                {{-- Botón menú lateral (móvil / tablet / escritorio) --}}
                <button
                    type="button"
                    @click="toggleSidebarFromHeader()"
                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                    :title="window.matchMedia('(min-width: 1024px)').matches ? (sidebarCollapsed ? 'Expandir menú' : 'Contraer menú') : (mobileSidebarOpen ? 'Cerrar menú' : 'Abrir menú')"
                    :aria-label="window.matchMedia('(min-width: 1024px)').matches ? (sidebarCollapsed ? 'Expandir menú lateral' : 'Contraer menú lateral') : (mobileSidebarOpen ? 'Cerrar menú lateral' : 'Abrir menú lateral')"
                    :aria-expanded="window.matchMedia('(min-width: 1024px)').matches ? !sidebarCollapsed : mobileSidebarOpen"
                >
                    <svg x-show="!mobileSidebarOpen" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-cloak x-show="mobileSidebarOpen" class="h-5 w-5 lg:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                {{-- Breadcrumb / título --}}
                    <div class="flex min-w-0 items-center gap-2">
                        <div class="hidden sm:flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-indigo-50">
                            <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </div>
                        <h1 class="truncate text-sm font-semibold text-slate-800">{{ $heading ?? $title ?? 'Inicio' }}</h1>
                    </div>
                </div>

                {{-- Derecha: acciones + perfil --}}
                <div class="flex items-center gap-2">

                    {{-- Indicador live --}}
                    <div class="hidden md:flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[11px] font-medium text-emerald-700">En línea</span>
                    </div>

                    {{-- Separador --}}
                    <div class="hidden md:block h-6 w-px bg-slate-200 mx-1"></div>

                    {{-- Menú de usuario --}}
                    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                        <button
                            type="button"
                            @click="open = !open"
                            class="flex items-center gap-2.5 rounded-xl pl-1 pr-3 py-1 hover:bg-slate-100 active:bg-slate-200 transition-all border border-transparent hover:border-slate-200"
                        >
                            @if($isComercio && $businessLogo)
                                <img src="{{ $businessLogo }}" alt="{{ $businessName }}"
                                     class="h-8 w-8 rounded-full object-cover shadow-sm ring-1 ring-slate-200">
                            @else
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-indigo-700 text-white text-xs font-bold shadow-sm">
                                    {{ $initials }}
                                </span>
                            @endif
                            <div class="hidden sm:block text-left min-w-0">
                                <p class="text-xs font-semibold text-slate-900 truncate max-w-[9rem] leading-tight">{{ $displayName }}</p>
                                @if($displayEmail)
                                    <p class="text-[11px] text-slate-400 truncate max-w-[9rem] leading-tight">{{ $displayEmail }}</p>
                                @endif
                            </div>
                            <svg class="h-3.5 w-3.5 text-slate-400 hidden sm:block shrink-0 transition-transform duration-150"
                                 :class="open ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        {{-- Dropdown --}}
                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            @click.away="open = false"
                            class="absolute right-0 mt-2 w-64 rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10 ring-1 ring-slate-900/5 z-50 overflow-hidden"
                            style="display:none"
                        >
                            {{-- Header del dropdown --}}
                            <div class="px-4 py-4 bg-gradient-to-br from-indigo-600 to-indigo-800">
                                <div class="flex items-center gap-3">
                                    @if($isComercio && $businessLogo)
                                        <img src="{{ $businessLogo }}" alt="{{ $businessName }}"
                                             class="h-10 w-10 shrink-0 rounded-full object-cover ring-2 ring-white/30">
                                    @else
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/20 text-white font-bold text-sm">
                                            {{ $initials }}
                                        </span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-white truncate">{{ $displayName }}</p>
                                        @if($displayEmail)
                                            <p class="text-xs text-indigo-200 truncate mt-0.5">{{ $displayEmail }}</p>
                                        @endif
                                        @if($u?->username)
                                            <p class="text-[11px] text-indigo-300 mt-0.5">{{ $u->username }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Rol badge --}}
                            @if($u?->getRoleNames()->first())
                            <div class="px-4 py-2.5 border-b border-slate-100 flex items-center gap-2">
                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span class="text-xs text-slate-500">Rol:</span>
                                <span class="text-xs font-medium text-slate-700">{{ $u->getRoleNames()->first() }}</span>
                            </div>
                            @endif

                            {{-- Cerrar sesión --}}
                            <div class="p-2">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Cerrar sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 lg:p-6 overflow-auto">
                {{ $slot }}
            </main>
        </div>
    </div>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @livewireScripts
    @stack('scripts')
    <x-livewire-alert::scripts />
</body>
</html>
