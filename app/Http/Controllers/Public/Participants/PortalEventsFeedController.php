<?php

namespace App\Http\Controllers\Public\Participants;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\Public\ParticipantsPortalAuthorization;
use App\Support\Public\PortalEventsFeedCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalEventsFeedController extends Controller
{
    public function __invoke(Request $request, string $businessToken): JsonResponse
    {
        $business = ParticipantsPortalAuthorization::requireBusiness(
            $businessToken,
            ParticipantsPortalAuthorization::EVENTS_ITEM
        );

        $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);

        $start = $request->string('start')->toString();
        $end = $request->string('end')->toString();

        $payload = PortalEventsFeedCache::remember(
            (int) $business->id,
            $start,
            $end,
            fn () => $this->buildFeed((int) $business->id, $businessToken, $start, $end),
            $request->boolean('fresh'),
        );

        return response()
            ->json($payload)
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }

    /** @return list<array<string, mixed>> */
    private function buildFeed(int $business_id, string $business_token, string $start, string $end): array
    {
        $events = Event::query()
            ->where('business_id', $business_id)
            ->with(['category:id,name', 'parent:id,name'])
            ->where('multi_day', false)
            ->where('active', true)
            ->whereDate('date_start', '>=', $start)
            ->whereDate('date_start', '<', $end)
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

        return $events->map(function (Event $event) use ($business_token) {
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
                    'businessToken' => $business_token,
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
        })->values()->all();
    }
}
