<div class="relative mx-auto w-full max-w-[90rem] p-4 sm:p-6"
    x-data
    x-init="
        const boot = () => {
            if (typeof window.initEventsScheduleCalendar !== 'function' || ! $refs.calendar) return;
            if (typeof $refs.calendar._destroyEventsScheduleCalendar === 'function') {
                $refs.calendar._destroyEventsScheduleCalendar();
            }
            window.initEventsScheduleCalendar($refs.calendar, {
                eventsUrl: @js($events_feed_url),
            });
        };
        $nextTick(boot);
    "
>
    <div class="pointer-events-none absolute -top-4 left-1/2 h-px w-[min(100%,48rem)] -translate-x-1/2 bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent" aria-hidden="true"></div>

    <nav class="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium text-slate-500">
        <a href="{{ route('dashboard') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Inicio</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.events.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Gestión de eventos</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.events.index') }}" wire:navigate class="rounded px-1.5 py-0.5 hover:bg-slate-200/60">Eventos</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-900">Agenda</span>
    </nav>

    <header class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 border-l-4 border-indigo-600 pl-4 sm:pl-5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-600/90">Gestión de eventos</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Agenda de eventos</h1>
                <p class="mt-2 max-w-xl text-sm text-slate-600">
                    Consulta los eventos por mes o año. Pulsa un evento para ver su información.
                </p>
            </div>
            <div class="flex w-full shrink-0 flex-wrap gap-2 sm:w-auto">
                <a href="{{ route('admin.events.index') }}" wire:navigate class="btn btn-outline-secondary btn-sm flex-1 justify-center sm:flex-none">
                    Volver
                </a>
                @if($can_manage_events)
                    <a href="{{ route('admin.events.manage.index') }}" wire:navigate class="btn btn-primary btn-sm flex-1 justify-center sm:flex-none">
                        Administrar eventos
                    </a>
                @endif
            </div>
        </div>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-3 shadow-sm ring-1 ring-slate-900/[0.035] sm:p-5">
        <div
            wire:ignore
            x-ref="calendar"
            class="events-schedule-calendar min-h-[28rem] text-sm text-slate-800 sm:min-h-[36rem]"
        ></div>
    </section>
</div>

@push('styles')
<style>
    .events-schedule-calendar .fc {
        --fc-border-color: rgb(226 232 240);
        --fc-button-bg-color: rgb(79 70 229);
        --fc-button-border-color: rgb(79 70 229);
        --fc-button-hover-bg-color: rgb(67 56 202);
        --fc-button-hover-border-color: rgb(67 56 202);
        --fc-button-active-bg-color: rgb(55 48 163);
        --fc-button-active-border-color: rgb(55 48 163);
        --fc-today-bg-color: rgb(238 242 255);
        --fc-event-bg-color: rgb(79 70 229);
        --fc-event-border-color: rgb(67 56 202);
        --fc-page-bg-color: transparent;
        font-family: inherit;
    }

    .events-schedule-calendar .fc .fc-toolbar {
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .events-schedule-calendar .fc .fc-toolbar-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: rgb(15 23 42);
    }

    .events-schedule-calendar .fc .fc-button {
        border-radius: 0.75rem;
        padding: 0.35rem 0.75rem;
        font-weight: 600;
        text-transform: none;
        box-shadow: none;
    }

    .events-schedule-calendar .fc .fc-daygrid-event {
        border-radius: 0.5rem;
        padding: 0.1rem 0.25rem;
        cursor: pointer;
    }

    .events-schedule-calendar .fc .fc-list-event:hover td {
        background: rgb(238 242 255);
        cursor: pointer;
    }

    @media (max-width: 640px) {
        .events-schedule-calendar .fc .fc-toolbar-title {
            font-size: 1rem;
            width: 100%;
            text-align: center;
        }

        .events-schedule-calendar .fc .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
            width: 100%;
        }
    }
</style>
@endpush
