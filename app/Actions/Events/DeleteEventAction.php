<?php

namespace App\Actions\Events;

use App\Actions\LogUserHistoricalAction;
use App\Models\Event;
use App\Support\EventsAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteEventAction
{
    use AsAction;

    public function handle(int $event_id): void
    {
        $event = Event::query()
            ->forAuthUser()
            ->whereNull('parent_id')
            ->findOrFail($event_id);

        if ($event->hasStartedAttendance()) {
            throw ValidationException::withMessages([
                'event' => 'No se puede eliminar: ya se inició la toma de asistencia de este evento.',
            ]);
        }

        EventsAccess::authorizeDeleteEvent($event);

        DB::transaction(function () use ($event) {
            $event->children()->each(function (Event $child) {
                $child->teams()->detach();
                $child->delete();
            });

            LogUserHistoricalAction::run(
                action: 'deleted',
                module: 'events.events',
                description: "Eliminó el evento {$event->name}",
                subject: $event,
                subject_label: $event->name,
                properties: [
                    'date_start' => $event->date_start?->toDateString(),
                    'date_end' => $event->date_end?->toDateString(),
                    'multi_day' => (bool) $event->multi_day,
                    'day' => $event->day,
                    'event_category_id' => $event->event_category_id,
                ],
                business_id: (int) $event->business_id,
            );

            $event->delete();
        });
    }
}
