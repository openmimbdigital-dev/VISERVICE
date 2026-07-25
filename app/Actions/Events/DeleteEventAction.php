<?php

namespace App\Actions\Events;

use App\Actions\LogUserHistoricalAction;
use App\Models\Event;
use App\Support\EventsAccess;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteEventAction
{
    use AsAction;

    public function handle(int $event_id): void
    {
        $event = Event::query()
            ->forAuthUser()
            ->findOrFail($event_id);

        EventsAccess::authorizeDeleteEvent($event);

        LogUserHistoricalAction::run(
            action: 'deleted',
            module: 'events.events',
            description: "Eliminó el evento {$event->name}",
            subject: $event,
            subject_label: $event->name,
            properties: [
                'date' => $event->date?->toDateString(),
                'day' => $event->day,
                'event_category_id' => $event->event_category_id,
            ],
            business_id: (int) $event->business_id,
        );

        $event->delete();
    }
}
