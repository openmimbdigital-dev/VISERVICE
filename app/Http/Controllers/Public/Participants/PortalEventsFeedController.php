<?php

namespace App\Http\Controllers\Public\Participants;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\Public\ParticipantsPortalAuthorization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalEventsFeedController extends Controller
{
    public function __invoke(Request $request, string $businessToken): JsonResponse
    {
        $business = ParticipantsPortalAuthorization::requireAuthenticatedBusiness(
            $businessToken,
            ParticipantsPortalAuthorization::EVENTS_ITEM
        );

        $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);

        $events = Event::query()
            ->where('business_id', $business->id)
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
            $events->map(function (Event $event) use ($businessToken) {
                $start_time = substr((string) $event->start_time, 0, 5);
                $end_time = substr((string) $event->end_time, 0, 5);
                $date = $event->date_start?->format('Y-m-d');

                return [
                    'id' => $event->id,
                    'title' => $event->name,
                    'start' => $date.'T'.$start_time.':00',
                    'end' => $date.'T'.$end_time.':00',
                    'allDay' => false,
                    'url' => route('public.participants.events.show', [
                        'businessToken' => $businessToken,
                        'event' => $event->id,
                    ]),
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
