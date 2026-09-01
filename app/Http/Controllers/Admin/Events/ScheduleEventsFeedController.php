<?php

namespace App\Http\Controllers\Admin\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\EventsAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleEventsFeedController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        EventsAccess::authorizeViewSchedule();

        $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);

        // Solo días “tomables”: eventos de un día y hijos de multi-día (no el padre).
        $events = Event::query()
            ->forAuthUser()
            ->with(['category:id,name', 'parent:id,name'])
            ->where('multi_day', false)
            ->where('active', true)
            ->whereDate('date_start', '>=', $request->string('start')->toString())
            ->whereDate('date_start', '<', $request->string('end')->toString())
            ->orderBy('date_start')
            ->orderBy('start_time')
            ->get([
                'id',
                'parent_id',
                'event_category_id',
                'name',
                'date_start',
                'date_end',
                'day',
                'start_time',
                'end_time',
            ]);

        return response()->json(
            $events->map(function (Event $event) {
                $start_time = substr((string) $event->start_time, 0, 5);
                $end_time = substr((string) $event->end_time, 0, 5);
                $date = $event->date_start?->format('Y-m-d');

                return [
                    'id' => $event->id,
                    'title' => $event->name,
                    'start' => $date.'T'.$start_time.':00',
                    'end' => $date.'T'.$end_time.':00',
                    'allDay' => false,
                    'url' => route('admin.events.schedule.show', $event),
                    'extendedProps' => [
                        'category' => $event->category?->name,
                        'day' => $event->day,
                        'time_label' => $event->scheduleRangeLabel(),
                        'is_child' => $event->parent_id !== null,
                        'parent_name' => $event->parent?->name,
                    ],
                ];
            })->values()
        );
    }
}
