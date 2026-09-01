<?php

namespace App\Actions\Events;

use App\Actions\LogUserHistoricalAction;
use App\Models\Event;
use App\Support\EventsAccess;
use Lorisleiva\Actions\Concerns\AsAction;

class CloseEventParticipationAction
{
    use AsAction;

    public function handle(int $event_id): Event
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $event = Event::query()
            ->forAuthUser($user)
            ->findOrFail($event_id);

        EventsAccess::authorizeCloseParticipation($event, $user);

        abort_unless((bool) $event->participation_enabled, 422, 'La toma de participación no está habilitada.');
        abort_unless(! $event->participation_closed, 422, 'La toma de participación ya está cerrada.');

        $event->update(['participation_closed' => true]);
        $event = $event->fresh();

        LogUserHistoricalAction::run(
            action: 'closed',
            module: 'events.participation',
            description: "Cerró la toma de participación del evento {$event->name}",
            subject: $event,
            subject_label: $event->name,
            properties: [
                'participation_closed' => true,
            ],
            business_id: (int) $event->business_id,
        );

        return $event;
    }
}
