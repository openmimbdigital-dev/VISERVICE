<div class="relative mx-auto w-full max-w-[90rem]">
    {{-- Acento superior sutil --}}
    <div
        class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent sm:-top-5"
        aria-hidden="true"
    ></div>

    {{-- Migas de pan --}}
    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500" aria-label="Migas de pan">
        <a
            href="{{ route('dashboard') }}"
            wire:navigate
            class="rounded-md px-1.5 py-0.5 transition hover:bg-slate-200/60 hover:text-slate-800"
        >Inicio</a>
        <span class="text-slate-300" aria-hidden="true">/</span>
        <span class="rounded-md bg-slate-200/50 px-1.5 py-0.5 text-slate-700">Administración</span>
        <span class="text-slate-300" aria-hidden="true">/</span>
        <span class="font-semibold text-slate-900">Usuarios</span>
    </nav>

    {{-- Cabecera de página --}}
    <header class="mb-8 lg:mb-10">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-stretch lg:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">
                    Directorio
                </p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-[1.65rem] sm:leading-tight">
                    Gestión de Usuarios
                </h1>
                <p class="mt-2 max-w-xl text-sm leading-relaxed text-slate-600">
                    Consulta, filtra y exporta el registro maestro de identidades. Los cambios aplican de inmediato en todo el sistema.
                </p>
            </div>

            {{-- KPIs --}}
            <div class="grid shrink-0 grid-cols-2 gap-3 sm:max-w-md sm:gap-4 lg:w-[22rem]">
                <div
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_8px_24px_-4px_rgba(15,23,42,0.08)] ring-1 ring-slate-900/[0.04]"
                >
                    <div class="absolute right-3 top-3 h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 opacity-90 transition group-hover:bg-indigo-100">
                        <svg class="m-1.5 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Total</p>
                    <p class="mt-2 text-3xl font-semibold tabular-nums tracking-tight text-slate-900">{{ number_format($usersTotal) }}</p>
                    <p class="mt-1 text-[11px] text-slate-400">Cuentas en el directorio</p>
                </div>
                <div
                    class="group relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_8px_24px_-4px_rgba(15,23,42,0.08)] ring-1 ring-slate-900/[0.04]"
                >
                    <div class="absolute right-3 top-3 h-8 w-8 rounded-lg bg-emerald-50 text-emerald-700 opacity-90 transition group-hover:bg-emerald-100">
                        <svg class="m-1.5 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Este mes</p>
                    <p class="mt-2 text-3xl font-semibold tabular-nums tracking-tight text-slate-900">{{ number_format($usersThisMonth) }}</p>
                    <p class="mt-1 text-[11px] text-slate-400">Altas del mes en curso</p>
                </div>
            </div>
        </div>
    </header>

    {{-- Contenedor de tabla --}}
    <section
        class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-[0_1px_2px_rgba(15,23,42,0.05),0_12px_32px_-8px_rgba(15,23,42,0.1)] ring-1 ring-slate-900/[0.035]"
        aria-labelledby="users-directory-heading"
    >
        <div class="flex flex-col gap-0 border-b border-slate-100 bg-gradient-to-r from-slate-50/90 via-white to-slate-50/80 px-5 py-4 sm:px-6 sm:py-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm shadow-indigo-600/25">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h2 id="users-directory-heading" class="text-base font-semibold text-slate-900">
                            Directorio de usuarios
                        </h2>
                        <p class="mt-0.5 text-sm text-slate-500">
                            Ordenación, búsqueda global y exportación integrada
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:shrink-0">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200/90 bg-white px-3 py-1 text-xs font-medium text-slate-600 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 ring-2 ring-emerald-500/25" title="Datos actualizados"></span>
                        Datos en tiempo real
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-slate-50/40 px-2 py-4 sm:px-4 sm:py-6">
            <div class="mx-auto max-w-full rounded-xl border border-slate-200/60 bg-white p-1 shadow-inner shadow-slate-900/[0.03] sm:p-2">
                <div class="min-w-0 overflow-x-auto rounded-lg [&_.divide-y]:divide-slate-100/90">
                    <livewire:admin.user.datatable-users />
                </div>
            </div>
        </div>
    </section>
</div>
