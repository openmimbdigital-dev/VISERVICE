<?php

namespace App\Actions\Events;

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

        $event->delete();
    }
}
