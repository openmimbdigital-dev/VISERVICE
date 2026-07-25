<?php

namespace App\Actions\Events;

use App\Models\Event;
use App\Support\ChurchEventsAccess;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteEventAction
{
    use AsAction;

    public function handle(int $event_id): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('events.events.delete'), 403);

        $event = Event::query()
            ->forAuthUser()
            ->findOrFail($event_id);

        abort_unless($event->canDelete(), 403);

        $event->delete();
    }
}
