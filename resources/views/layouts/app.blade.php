<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'VISERVICE' }} — VISERVICE</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
    </style>
    @stack('head')
</head>
<body class="bg-slate-100 text-slate-900 antialiased min-h-screen">
    @php
        $u = $user ?? auth()->user();
        $displayName = $u?->name ?? $u?->username ?? $u?->email ?? 'Usuario';
        $displayEmail = $u?->email ?? '';
        $initials = strtoupper(mb_substr(preg_replace('/\s+/', '', $displayName), 0, 2));
        if (mb_strlen($displayName) >= 2 && preg_match('/\s/u', $displayName)) {
            $parts = preg_split('/\s+/u', trim($displayName));
            $initials = mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[count($parts) - 1] ?? '', 0, 1));
        }
    @endphp

    <div
        class="min-h-screen flex"
        x-data="{
            sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
            usersNavOpen: localStorage.getItem('sidebarNavUsersOpen') !== 'false',
            usersCollapsedOpen: false,
            toggleSidebar() {
                this.sidebarCollapsed = !this.sidebarCollapsed;
                localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
                this.usersCollapsedOpen = false;
            },
            toggleUsersNav() {
                this.usersNavOpen = !this.usersNavOpen;
                localStorage.setItem('sidebarNavUsersOpen', this.usersNavOpen);
            },
            toggleUsersCollapsedMenu() {
                this.usersCollapsedOpen = !this.usersCollapsedOpen;
            },
            closeUsersCollapsedMenu() {
                this.usersCollapsedOpen = false;
            }
        }"
        @if (request()->routeIs('admin.users.*', 'admin.roles.*'))
            x-init="usersNavOpen = true"
        @endif
        @keydown.escape.window="if (usersCollapsedOpen) closeUsersCollapsedMenu()"
    >
        {{-- Sidebar --}}
        <aside
            class="relative z-40 flex flex-col bg-slate-900 text-slate-100 shrink-0 border-r border-slate-800 transition-[width] duration-200 ease-out overflow-x-visible overflow-y-visible"
            :class="sidebarCollapsed ? 'w-[4.25rem]' : 'w-60'"
        >
            <div class="h-14 flex items-center px-3 border-b border-slate-800/80 gap-2">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white font-bold text-sm">
                    V
                </div>
                <div class="min-w-0 flex-1" x-show="!sidebarCollapsed" x-transition.opacity>
                    <p class="font-semibold text-white truncate text-sm">VISERVICE</p>
                    <p class="text-[11px] text-slate-400 truncate">Panel</p>
                </div>
            </div>

            <nav class="flex-1 min-h-0 space-y-1 overflow-y-auto overflow-x-visible py-4 px-2">
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
                    <span class="truncate" x-show="!sidebarCollapsed" x-transition.opacity>Inicio</span>
                </a>

                {{-- Usuarios colapsado: clic despliega subopciones hacia abajo (solo iconos) --}}
                <div
                    x-cloak
                    x-show="sidebarCollapsed"
                    class="relative"
                    @click.outside="closeUsersCollapsedMenu()"
                >
                    <button
                        type="button"
                        @click.stop="toggleUsersCollapsedMenu()"
                        class="flex w-full items-center justify-center rounded-lg px-2.5 py-2.5 text-sm font-medium transition
                            {{ request()->routeIs('admin.users.*', 'admin.roles.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                        title="Usuarios"
                        :aria-expanded="usersCollapsedOpen"
                    >
                        <svg class="h-5 w-5 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </button>
                    <div
                        x-show="usersCollapsedOpen"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 -translate-y-0.5"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="absolute left-0 right-0 top-full z-[100] mt-1 flex flex-col gap-0.5 rounded-xl border border-slate-700/90 bg-slate-900 p-1 shadow-lg ring-1 ring-white/5"
                    >
                        <a
                            href="{{ route('admin.users.index') }}"
                            wire:navigate
                            aria-label="Ver usuarios"
                            @click="closeUsersCollapsedMenu()"
                            class="flex items-center justify-center rounded-lg py-2.5 transition
                                {{ request()->routeIs('admin.users.index') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                            title="Ver usuarios"
                        >
                            <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                        </a>
                        <a
                            href="{{ route('admin.roles.index') }}"
                            wire:navigate
                            aria-label="Ver roles"
                            @click="closeUsersCollapsedMenu()"
                            class="flex items-center justify-center rounded-lg py-2.5 transition
                                {{ request()->routeIs('admin.roles.index') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                            title="Ver roles"
                        >
                            <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                <div x-show="!sidebarCollapsed" x-cloak class="space-y-0.5">
                    <button
                        type="button"
                        @click="toggleUsersNav()"
                        class="w-full flex items-center gap-3 rounded-lg px-2.5 py-2.5 text-sm font-medium transition text-left
                            {{ request()->routeIs('admin.users.*', 'admin.roles.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                    >
                        <svg class="h-5 w-5 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span class="truncate flex-1">Usuarios</span>
                        <svg
                            class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200"
                            :class="usersNavOpen ? 'rotate-180' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="usersNavOpen" class="mt-0.5 space-y-0.5 border-l border-slate-700/80 ml-4 pl-3">
                        <a
                            href="{{ route('admin.users.index') }}"
                            wire:navigate
                            class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition
                                {{ request()->routeIs('admin.users.index') ? 'bg-slate-800/90 text-white font-medium' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}"
                            title="Ver usuarios"
                        >
                            <svg class="h-4 w-4 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            <span class="truncate">Ver usuarios</span>
                        </a>
                        <a
                            href="{{ route('admin.roles.index') }}"
                            wire:navigate
                            class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition
                                {{ request()->routeIs('admin.roles.index') ? 'bg-slate-800/90 text-white font-medium' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}"
                            title="Ver roles"
                        >
                            <svg class="h-4 w-4 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <span class="truncate">Ver roles</span>
                        </a>
                    </div>
                </div>
            </nav>

            <div class="p-2 border-t border-slate-800/80">
                <button
                    type="button"
                    @click="toggleSidebar()"
                    class="w-full flex items-center justify-center gap-2 rounded-lg py-2 text-slate-400 hover:bg-slate-800 hover:text-white transition text-sm"
                    :title="sidebarCollapsed ? 'Expandir menú' : 'Encoger menú'"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Encoger</span>
                </button>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex-1 flex flex-col min-w-0 min-h-screen">
            <header class="h-14 bg-white border-b border-slate-200 flex items-center justify-between px-4 lg:px-6 shrink-0">
                <div class="min-w-0">
                    <h1 class="text-lg font-semibold text-slate-900 truncate">{{ $heading ?? $title ?? 'Inicio' }}</h1>
                </div>

                <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex items-center gap-2 rounded-lg pl-1 pr-2 py-1 hover:bg-slate-100 transition border border-transparent hover:border-slate-200"
                    >
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 text-sm font-semibold">
                            {{ $initials }}
                        </span>
                        <div class="hidden sm:block text-left min-w-0">
                            <p class="text-sm font-medium text-slate-900 truncate max-w-[10rem]">{{ $displayName }}</p>
                            @if($displayEmail)
                                <p class="text-xs text-slate-500 truncate max-w-[10rem]">{{ $displayEmail }}</p>
                            @endif
                        </div>
                        <svg class="h-4 w-4 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        x-transition.opacity
                        @click.away="open = false"
                        class="absolute right-0 mt-2 w-64 rounded-xl border border-slate-200 bg-white shadow-lg py-2 z-50"
                        style="display: none;"
                    >
                        <div class="px-4 py-3 border-b border-slate-100">
                            <p class="text-sm font-semibold text-slate-900 truncate">{{ $displayName }}</p>
                            @if($displayEmail)
                                <p class="text-xs text-slate-500 truncate mt-0.5">{{ $displayEmail }}</p>
                            @endif
                            @if($u?->username)
                                <p class="text-xs text-slate-500 mt-1">Usuario: {{ $u->username }}</p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 lg:p-6 overflow-auto">
                {{ $slot }}
            </main>
        </div>
    </div>
    @stack('scripts')
    @livewireScripts
</body>
</html>
