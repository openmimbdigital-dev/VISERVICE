import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import esLocale from '@fullcalendar/core/locales/es';

function navigateTo(url) {
    if (!url) {
        return;
    }

    if (window.Livewire?.navigate) {
        window.Livewire.navigate(url);
        return;
    }

    window.location.href = url;
}

/**
 * @param {HTMLElement} element
 * @param {{ eventsUrl: string }} options
 */
export function initEventsScheduleCalendar(element, options) {
    if (!element || element.dataset.calendarReady === '1') {
        return element._eventsScheduleCalendar ?? null;
    }

    element.dataset.calendarReady = '1';

    const isMobile = () => window.matchMedia('(max-width: 640px)').matches;

    const calendar = new Calendar(element, {
        plugins: [dayGridPlugin, interactionPlugin, listPlugin],
        locale: esLocale,
        initialView: isMobile() ? 'listMonth' : 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listMonth',
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            list: 'Lista',
        },
        height: 'auto',
        navLinks: true,
        editable: false,
        selectable: false,
        dayMaxEvents: 3,
        moreLinkText: 'más',
        events: {
            url: options.eventsUrl,
            method: 'GET',
            failure() {
                console.error('No se pudieron cargar los eventos de la agenda.');
            },
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        },
        eventClick(info) {
            info.jsEvent.preventDefault();
            navigateTo(info.event.url);
        },
        dateClick(info) {
            const dayEvents = calendar.getEvents().filter((event) => {
                const start = event.startStr ?? '';

                return start.startsWith(info.dateStr);
            });

            if (dayEvents.length === 1) {
                navigateTo(dayEvents[0].url);
                return;
            }

            if (dayEvents.length > 1) {
                calendar.changeView('listDay', info.dateStr);
            }
        },
        eventDidMount(info) {
            const category = info.event.extendedProps.category;
            const timeLabel = info.event.extendedProps.time_label;
            const parts = [info.event.title];

            if (timeLabel) {
                parts.push(timeLabel);
            }

            if (category) {
                parts.push(category);
            }

            info.el.setAttribute('title', parts.join(' · '));
        },
    });

    calendar.render();
    element._eventsScheduleCalendar = calendar;

    const media = window.matchMedia('(max-width: 640px)');
    const onViewportChange = (event) => {
        const current = calendar.view.type;
        if (event.matches && current === 'dayGridMonth') {
            calendar.changeView('listMonth');
        } else if (! event.matches && current === 'listMonth') {
            calendar.changeView('dayGridMonth');
        }
    };
    media.addEventListener('change', onViewportChange);

    element._destroyEventsScheduleCalendar = () => {
        media.removeEventListener('change', onViewportChange);
        calendar.destroy();
        delete element.dataset.calendarReady;
        delete element._eventsScheduleCalendar;
    };

    return calendar;
}

window.initEventsScheduleCalendar = initEventsScheduleCalendar;
