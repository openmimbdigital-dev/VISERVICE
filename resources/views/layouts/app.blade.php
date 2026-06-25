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
            usersNavOpen: localStorage.getItem('sidebarNavUsersOpen') !== 'false',
            usersCollapsedOpen: false,
            subsNavOpen: localStorage.getItem('sidebarNavSubsOpen') !== 'false',
            subsCollapsedOpen: false,
            workshopNavOpen: localStorage.getItem('sidebarNavWorkshopOpen') !== 'false',
            workshopCollapsedOpen: false,
            catalogNavOpen: localStorage.getItem('sidebarNavCatalogOpen') !== 'false',
            catalogCollapsedOpen: false,
            settingsNavOpen: localStorage.getItem('sidebarNavSettingsOpen') !== 'false',
            settingsCollapsedOpen: false,
            toggleSidebar() {
                this.sidebarCollapsed = !this.sidebarCollapsed;
                localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
                this.usersCollapsedOpen = false;
                this.subsCollapsedOpen = false;
                this.workshopCollapsedOpen = false;
                this.catalogCollapsedOpen = false;
                this.settingsCollapsedOpen = false;
            },
            toggleMobileSidebar() {
                this.mobileSidebarOpen = !this.mobileSidebarOpen;
                if (this.mobileSidebarOpen) {
                    this.usersCollapsedOpen = false;
                    this.subsCollapsedOpen = false;
                    this.workshopCollapsedOpen = false;
                    this.catalogCollapsedOpen = false;
                    this.settingsCollapsedOpen = false;
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
            toggleUsersNav() {
                this.usersNavOpen = !this.usersNavOpen;
                localStorage.setItem('sidebarNavUsersOpen', this.usersNavOpen);
            },
            toggleUsersCollapsedMenu() {
                this.usersCollapsedOpen = !this.usersCollapsedOpen;
                this.subsCollapsedOpen = false;
            },
            closeUsersCollapsedMenu() {
                this.usersCollapsedOpen = false;
            },
            toggleSubsNav() {
                this.subsNavOpen = !this.subsNavOpen;
                localStorage.setItem('sidebarNavSubsOpen', this.subsNavOpen);
            },
            toggleSubsCollapsedMenu() {
                this.subsCollapsedOpen = !this.subsCollapsedOpen;
                this.usersCollapsedOpen = false;
                this.workshopCollapsedOpen = false;
                this.catalogCollapsedOpen = false;
                this.settingsCollapsedOpen = false;
            },
            closeSubsCollapsedMenu() {
                this.subsCollapsedOpen = false;
            },
            toggleWorkshopNav() {
                this.workshopNavOpen = !this.workshopNavOpen;
                localStorage.setItem('sidebarNavWorkshopOpen', this.workshopNavOpen);
            },
            toggleWorkshopCollapsedMenu() {
                this.workshopCollapsedOpen = !this.workshopCollapsedOpen;
                this.usersCollapsedOpen = false;
                this.subsCollapsedOpen = false;
                this.catalogCollapsedOpen = false;
                this.settingsCollapsedOpen = false;
            },
            closeWorkshopCollapsedMenu() {
                this.workshopCollapsedOpen = false;
            },
            toggleCatalogNav() {
                this.catalogNavOpen = !this.catalogNavOpen;
                localStorage.setItem('sidebarNavCatalogOpen', this.catalogNavOpen);
            },
            toggleCatalogCollapsedMenu() {
                this.catalogCollapsedOpen = !this.catalogCollapsedOpen;
                this.usersCollapsedOpen = false;
                this.subsCollapsedOpen = false;
                this.workshopCollapsedOpen = false;
                this.settingsCollapsedOpen = false;
            },
            closeCatalogCollapsedMenu() {
                this.catalogCollapsedOpen = false;
            },
            toggleSettingsNav() {
                this.settingsNavOpen = !this.settingsNavOpen;
                localStorage.setItem('sidebarNavSettingsOpen', this.settingsNavOpen);
            },
            toggleSettingsCollapsedMenu() {
                this.settingsCollapsedOpen = !this.settingsCollapsedOpen;
                this.usersCollapsedOpen = false;
                this.subsCollapsedOpen = false;
                this.workshopCollapsedOpen = false;
                this.catalogCollapsedOpen = false;
            },
            closeSettingsCollapsedMenu() {
                this.settingsCollapsedOpen = false;
            },
            showSidebarLabels() {
                return !this.sidebarCollapsed || this.mobileSidebarOpen;
            },
            showSidebarIconsOnly() {
                return this.sidebarCollapsed && !this.mobileSidebarOpen;
            }
        }"
        x-init="
            @if (request()->routeIs('admin.users.*', 'admin.roles.*')) usersNavOpen = true; @endif
            @if (request()->routeIs('admin.subscriptions.*', 'admin.payments.*', 'admin.bank-accounts.*', 'admin.banks.*', 'admin.businesses.*', 'admin.finance.*')) subsNavOpen = true; @endif
            @if (request()->routeIs('admin.workshop.clients.*', 'admin.workshop.equipment.*', 'admin.workshop.quotations.*', 'admin.workshop.work-orders.*')) workshopNavOpen = true; @endif
            @if (request()->routeIs('admin.workshop.catalog.*')) catalogNavOpen = true; @endif
            @if (request()->routeIs('admin.settings.*')) settingsNavOpen = true; @endif
        "
        @keydown.escape.window="closeMobileSidebar(); if (usersCollapsedOpen) closeUsersCollapsedMenu(); if (subsCollapsedOpen) closeSubsCollapsedMenu(); if (workshopCollapsedOpen) closeWorkshopCollapsedMenu(); if (catalogCollapsedOpen) closeCatalogCollapsedMenu(); if (settingsCollapsedOpen) closeSettingsCollapsedMenu()"
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

                @can('users.view')
                {{-- Usuarios colapsado: clic despliega subopciones hacia abajo (solo iconos) --}}
                <div
                    x-cloak
                    x-show="showSidebarIconsOnly()"
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
                        @role('superAdmin')
                        <a
                            href="{{ route('admin.roles.index') }}"
                            wire:navigate
                            aria-label="Ver roles"
                            @click="closeUsersCollapsedMenu()"
                            class="flex items-center justify-center rounded-lg py-2.5 transition
                                {{ request()->routeIs('admin.roles.index') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                            title="Roles y permisos"
                        >
                            <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </a>
                        @endrole
                    </div>
                </div>

                <div x-show="showSidebarLabels()" x-cloak class="space-y-0.5">
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
                        @role('superAdmin')
                        <a
                            href="{{ route('admin.roles.index') }}"
                            wire:navigate
                            class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition
                                {{ request()->routeIs('admin.roles.index') ? 'bg-slate-800/90 text-white font-medium' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}"
                            title="Roles y permisos"
                        >
                            <svg class="h-4 w-4 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <span class="truncate">Roles y permisos</span>
                        </a>
                        @endrole
                    </div>
                </div>
                @endcan

                {{-- Suscripciones (solo superAdmin) --}}
                @role('superAdmin')

                {{-- Colapsado: popup dropdown --}}
                <div
                    x-cloak
                    x-show="showSidebarIconsOnly()"
                    class="relative"
                    @click.outside="closeSubsCollapsedMenu()"
                >
                    <button
                        type="button"
                        @click.stop="toggleSubsCollapsedMenu()"
                        class="flex w-full items-center justify-center rounded-lg px-2.5 py-2.5 text-sm font-medium transition
                            {{ request()->routeIs('admin.subscriptions.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                        title="Suscripciones"
                        :aria-expanded="subsCollapsedOpen"
                    >
                        <svg class="h-5 w-5 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </button>
                    <div
                        x-show="subsCollapsedOpen"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 -translate-y-0.5"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="absolute left-0 right-0 top-full z-[100] mt-1 flex flex-col gap-0.5 rounded-xl border border-slate-700/90 bg-slate-900 p-1 shadow-lg ring-1 ring-white/5"
                    >
                        <a
                            href="{{ route('admin.finance.index') }}"
                            wire:navigate
                            @click="closeSubsCollapsedMenu()"
                            class="flex items-center justify-center rounded-lg py-2.5 transition
                                {{ request()->routeIs('admin.finance.index') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                            title="Panel Financiero"
                        >
                            <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </a>
                        <a
                            href="{{ route('admin.subscriptions.index') }}"
                            wire:navigate
                            @click="closeSubsCollapsedMenu()"
                            class="flex items-center justify-center rounded-lg py-2.5 transition
                                {{ request()->routeIs('admin.subscriptions.index') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                            title="Ver suscripciones"
                        >
                            <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                        </a>
                        <a
                            href="{{ route('admin.subscriptions.plans.index') }}"
                            wire:navigate
                            @click="closeSubsCollapsedMenu()"
                            class="flex items-center justify-center rounded-lg py-2.5 transition
                                {{ request()->routeIs('admin.subscriptions.plans.index') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                            title="Planes"
                        >
                            <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </a>
                        <a
                            href="{{ route('admin.payments.index') }}"
                            wire:navigate
                            @click="closeSubsCollapsedMenu()"
                            class="flex items-center justify-center rounded-lg py-2.5 transition
                                {{ request()->routeIs('admin.payments.index') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                            title="Pagos pendientes"
                        >
                            <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </a>
                        <a
                            href="{{ route('admin.bank-accounts.index') }}"
                            wire:navigate
                            @click="closeSubsCollapsedMenu()"
                            class="flex items-center justify-center rounded-lg py-2.5 transition
                                {{ request()->routeIs('admin.bank-accounts.index') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                            title="Cuentas bancarias"
                        >
                            <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                            </svg>
                        </a>
                        <a
                            href="{{ route('admin.banks.index') }}"
                            wire:navigate
                            @click="closeSubsCollapsedMenu()"
                            class="flex items-center justify-center rounded-lg py-2.5 transition
                                {{ request()->routeIs('admin.banks.index') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                            title="Bancos"
                        >
                            <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </a>
                        <a
                            href="{{ route('admin.businesses.index') }}"
                            wire:navigate
                            @click="closeSubsCollapsedMenu()"
                            class="flex items-center justify-center rounded-lg py-2.5 transition
                                {{ request()->routeIs('admin.businesses.index') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                            title="Negocios registrados"
                        >
                            <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Expandido: acordeón --}}
                <div x-show="showSidebarLabels()" x-cloak class="space-y-0.5">
                    <button
                        type="button"
                        @click="toggleSubsNav()"
                        class="w-full flex items-center gap-3 rounded-lg px-2.5 py-2.5 text-sm font-medium transition text-left
                            {{ request()->routeIs('admin.subscriptions.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                    >
                        <svg class="h-5 w-5 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <span class="truncate flex-1">Suscripciones</span>
                        <svg
                            class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200"
                            :class="subsNavOpen ? 'rotate-180' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="subsNavOpen" class="mt-0.5 space-y-0.5 border-l border-slate-700/80 ml-4 pl-3">
                        <a
                            href="{{ route('admin.finance.index') }}"
                            wire:navigate
                            class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition
                                {{ request()->routeIs('admin.finance.index') ? 'bg-slate-800/90 text-white font-medium' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}"
                            title="Panel Financiero"
                        >
                            <svg class="h-4 w-4 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span class="truncate">Finanzas</span>
                        </a>
                        <a
                            href="{{ route('admin.subscriptions.index') }}"
                            wire:navigate
                            class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition
                                {{ request()->routeIs('admin.subscriptions.index') ? 'bg-slate-800/90 text-white font-medium' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}"
                            title="Ver suscripciones"
                        >
                            <svg class="h-4 w-4 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            <span class="truncate">Ver suscripciones</span>
                        </a>
                        <a
                            href="{{ route('admin.subscriptions.plans.index') }}"
                            wire:navigate
                            class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition
                                {{ request()->routeIs('admin.subscriptions.plans.index') ? 'bg-slate-800/90 text-white font-medium' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}"
                            title="Planes"
                        >
                            <svg class="h-4 w-4 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span class="truncate">Planes</span>
                        </a>
                        <a
                            href="{{ route('admin.payments.index') }}"
                            wire:navigate
                            class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition
                                {{ request()->routeIs('admin.payments.index') ? 'bg-slate-800/90 text-white font-medium' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}"
                            title="Pagos pendientes"
                        >
                            <svg class="h-4 w-4 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="truncate">Pagos pendientes</span>
                            @php $pendingCount = \App\Models\SubscriptionInvoice::where('status','pending')->whereHas('subscription', fn($q) => $q->where('status','pending'))->count(); @endphp
                            @if($pendingCount > 0)
                                <span class="ml-auto shrink-0 inline-flex items-center justify-center h-5 min-w-5 px-1.5 rounded-full bg-amber-500 text-white text-[10px] font-bold">{{ $pendingCount }}</span>
                            @endif
                        </a>
                        <a
                            href="{{ route('admin.bank-accounts.index') }}"
                            wire:navigate
                            class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition
                                {{ request()->routeIs('admin.bank-accounts.index') ? 'bg-slate-800/90 text-white font-medium' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}"
                            title="Cuentas bancarias"
                        >
                            <svg class="h-4 w-4 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                            </svg>
                            <span class="truncate">Cuentas bancarias</span>
                        </a>
                        <a
                            href="{{ route('admin.banks.index') }}"
                            wire:navigate
                            class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition
                                {{ request()->routeIs('admin.banks.index') ? 'bg-slate-800/90 text-white font-medium' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}"
                            title="Bancos"
                        >
                            <svg class="h-4 w-4 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="truncate">Bancos</span>
                        </a>
                        <a
                            href="{{ route('admin.businesses.index') }}"
                            wire:navigate
                            class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition
                                {{ request()->routeIs('admin.businesses.index') ? 'bg-slate-800/90 text-white font-medium' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}"
                            title="Negocios registrados"
                        >
                            <svg class="h-4 w-4 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="truncate">Negocios</span>
                        </a>
                    </div>
                </div>

                @endrole

                {{-- Mi Negocio (solo rol Comercio) --}}
                @role('Comercio')
                {{-- Colapsado --}}
                <div x-cloak x-show="showSidebarIconsOnly()">
                    <a href="{{ route('comercio.business.edit') }}" wire:navigate
                        class="flex w-full items-center justify-center rounded-lg px-2.5 py-2.5 text-sm font-medium transition
                            {{ request()->routeIs('comercio.business.edit') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                        title="Mi Negocio">
                        <svg class="h-5 w-5 shrink-0 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </a>
                </div>
                {{-- Expandido --}}
                <div x-show="showSidebarLabels()" x-cloak>
                    <a href="{{ route('comercio.business.edit') }}" wire:navigate
                        class="flex items-center gap-3 rounded-lg px-2.5 py-2.5 text-sm font-medium transition
                            {{ request()->routeIs('comercio.business.edit') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span class="truncate">Mi Negocio</span>
                    </a>
                </div>
                @endrole

                {{-- === TALLER === --}}
                {{-- Colapsado --}}
                <div x-cloak x-show="showSidebarIconsOnly()" class="relative" @click.outside="closeWorkshopCollapsedMenu()">
                    <button type="button" @click.stop="toggleWorkshopCollapsedMenu()"
                        class="flex w-full items-center justify-center rounded-lg px-2.5 py-2.5 text-sm font-medium transition
                            {{ request()->routeIs('admin.workshop.clients.*','admin.workshop.equipment.*','admin.workshop.quotations.*','admin.workshop.work-orders.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                        title="Taller" :aria-expanded="workshopCollapsedOpen">
                        <svg class="h-5 w-5 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </button>
                    <div x-show="workshopCollapsedOpen" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-0.5" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        class="absolute left-0 right-0 top-full z-[100] mt-1 flex flex-col gap-0.5 rounded-xl border border-slate-700/90 bg-slate-900 p-1 shadow-lg ring-1 ring-white/5">
                        @foreach([['route'=>'admin.workshop.clients.index','title'=>'Clientes','path'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z','permission'=>'workshop.clients.view'],['route'=>'admin.workshop.equipment.index','title'=>'Equipos','path'=>'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z'],['route'=>'admin.workshop.quotations.index','title'=>'Cotizaciones','path'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],['route'=>'admin.workshop.work-orders.index','title'=>'OTs','path'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01']] as $item)
                        @if(empty($item['permission']) || auth()->user()->can($item['permission']))
                        <a href="{{ route($item['route']) }}" wire:navigate @click="closeWorkshopCollapsedMenu()"
                            class="flex items-center justify-center rounded-lg py-2.5 transition {{ request()->routeIs($item['route']) ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                            title="{{ $item['title'] }}">
                            <svg class="h-5 w-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['path'] }}"/></svg>
                        </a>
                        @endif
                        @endforeach
                    </div>
                </div>

                {{-- Expandido --}}
                <div x-show="showSidebarLabels()" x-cloak class="space-y-0.5">
                    <button type="button" @click="toggleWorkshopNav()"
                        class="w-full flex items-center gap-3 rounded-lg px-2.5 py-2.5 text-sm font-medium transition text-left
                            {{ request()->routeIs('admin.workshop.clients.*','admin.workshop.equipment.*','admin.workshop.quotations.*','admin.workshop.work-orders.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="truncate flex-1">Taller</span>
                        <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200" :class="workshopNavOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="workshopNavOpen" class="mt-0.5 space-y-0.5 border-l border-slate-700/80 ml-4 pl-3">
                        @foreach([['route'=>'admin.workshop.clients.index','label'=>'Clientes','path'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z','permission'=>'workshop.clients.view'],['route'=>'admin.workshop.equipment.index','label'=>'Equipos','path'=>'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z'],['route'=>'admin.workshop.quotations.index','label'=>'Cotizaciones','path'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],['route'=>'admin.workshop.work-orders.index','label'=>'Órdenes de Trabajo','path'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01']] as $item)
                        @if(empty($item['permission']) || auth()->user()->can($item['permission']))
                        <a href="{{ route($item['route']) }}" wire:navigate
                            class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition {{ request()->routeIs($item['route']) ? 'bg-slate-800/90 text-white font-medium' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="h-4 w-4 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['path'] }}"/></svg>
                            <span class="truncate">{{ $item['label'] }}</span>
                        </a>
                        @endif
                        @endforeach
                    </div>
                </div>

                {{-- === CATÁLOGO === --}}
                {{-- Colapsado --}}
                <div x-cloak x-show="showSidebarIconsOnly()" class="relative" @click.outside="closeCatalogCollapsedMenu()">
                    <button type="button" @click.stop="toggleCatalogCollapsedMenu()"
                        class="flex w-full items-center justify-center rounded-lg px-2.5 py-2.5 text-sm font-medium transition
                            {{ request()->routeIs('admin.workshop.catalog.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                        title="Catálogo" :aria-expanded="catalogCollapsedOpen">
                        <svg class="h-5 w-5 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </button>
                    <div x-show="catalogCollapsedOpen" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-0.5" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        class="absolute left-0 right-0 top-full z-[100] mt-1 flex flex-col gap-0.5 rounded-xl border border-slate-700/90 bg-slate-900 p-1 shadow-lg ring-1 ring-white/5">
                        <a href="{{ route('admin.workshop.catalog.services.index') }}" wire:navigate @click="closeCatalogCollapsedMenu()"
                            class="flex items-center justify-center rounded-lg py-2.5 transition {{ request()->routeIs('admin.workshop.catalog.services.index') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                            title="Servicios">
                            <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </a>
                        <a href="{{ route('admin.workshop.catalog.spare-parts.index') }}" wire:navigate @click="closeCatalogCollapsedMenu()"
                            class="flex items-center justify-center rounded-lg py-2.5 transition {{ request()->routeIs('admin.workshop.catalog.spare-parts.index') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                            title="Repuestos">
                            <svg class="h-5 w-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </a>
                    </div>
                </div>

                {{-- Expandido --}}
                <div x-show="showSidebarLabels()" x-cloak class="space-y-0.5">
                    <button type="button" @click="toggleCatalogNav()"
                        class="w-full flex items-center gap-3 rounded-lg px-2.5 py-2.5 text-sm font-medium transition text-left
                            {{ request()->routeIs('admin.workshop.catalog.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span class="truncate flex-1">Catálogo</span>
                        <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200" :class="catalogNavOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="catalogNavOpen" class="mt-0.5 space-y-0.5 border-l border-slate-700/80 ml-4 pl-3">
                        <a href="{{ route('admin.workshop.catalog.services.index') }}" wire:navigate
                            class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition {{ request()->routeIs('admin.workshop.catalog.services.index') ? 'bg-slate-800/90 text-white font-medium' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="h-4 w-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            <span class="truncate">Servicios</span>
                        </a>
                        <a href="{{ route('admin.workshop.catalog.spare-parts.index') }}" wire:navigate
                            class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition {{ request()->routeIs('admin.workshop.catalog.spare-parts.index') ? 'bg-slate-800/90 text-white font-medium' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="h-4 w-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="truncate">Repuestos</span>
                        </a>
                    </div>
                </div>

                @can('settings.view')
                {{-- === CONFIGURACIÓN === --}}
                {{-- Colapsado --}}
                <div x-cloak x-show="showSidebarIconsOnly()" class="relative" @click.outside="closeSettingsCollapsedMenu()">
                    <button type="button" @click.stop="toggleSettingsCollapsedMenu()"
                        class="flex w-full items-center justify-center rounded-lg px-2.5 py-2.5 text-sm font-medium transition
                            {{ request()->routeIs('admin.settings.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}"
                        title="Configuración" :aria-expanded="settingsCollapsedOpen">
                        <svg class="h-5 w-5 shrink-0 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </button>
                    <div x-show="settingsCollapsedOpen" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-0.5" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        class="absolute left-0 right-0 top-full z-[100] mt-1 flex flex-col gap-0.5 rounded-xl border border-slate-700/90 bg-slate-900 p-1 shadow-lg ring-1 ring-white/5">
                        <a href="{{ route('admin.settings.equipment.index') }}" wire:navigate @click="closeSettingsCollapsedMenu()"
                            class="flex items-center justify-center rounded-lg py-2.5 transition {{ request()->routeIs('admin.settings.equipment.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                            title="Equipos">
                            <svg class="h-5 w-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </a>
                    </div>
                </div>

                {{-- Expandido --}}
                <div x-show="showSidebarLabels()" x-cloak class="space-y-0.5">
                    <button type="button" @click="toggleSettingsNav()"
                        class="w-full flex items-center gap-3 rounded-lg px-2.5 py-2.5 text-sm font-medium transition text-left
                            {{ request()->routeIs('admin.settings.*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="truncate flex-1">Configuración</span>
                        <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform duration-200" :class="settingsNavOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="settingsNavOpen" class="mt-0.5 space-y-0.5 border-l border-slate-700/80 ml-4 pl-3">
                        <a href="{{ route('admin.settings.equipment.index') }}" wire:navigate
                            class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm transition {{ request()->routeIs('admin.settings.equipment.*') ? 'bg-slate-800/90 text-white font-medium' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="h-4 w-4 shrink-0 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span class="truncate">Equipos</span>
                        </a>
                    </div>
                </div>
                @endcan
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
