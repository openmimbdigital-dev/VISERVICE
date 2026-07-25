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

        $events = Event::query()
            ->forAuthUser()
            ->with(['category:id,name'])
            ->whereDate('date', '>=', $request->string('start')->toString())
            ->whereDate('date', '<', $request->string('end')->toString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get(['id', 'event_category_id', 'name', 'date', 'day', 'start_time', 'end_time']);

        return response()->json(
            $events->map(function (Event $event) {
                $start_time = substr((string) $event->start_time, 0, 5);
                $end_time = substr((string) $event->end_time, 0, 5);
                $date = $event->date?->format('Y-m-d');

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
                        'time_label' => $start_time.' – '.$end_time,
                    ],
                ];
            })->values()
        );
    }
}
