<?php

namespace App\Actions\Events;

use App\Models\Event;
use App\Support\EventsAccess;
use Lorisleiva\Actions\Concerns\AsAction;

class ReopenEventAttendanceAction
{
    use AsAction;

    public function handle(int $event_id): Event
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $event = Event::query()
            ->forAuthUser($user)
            ->findOrFail($event_id);

        EventsAccess::authorizeCloseAttendance($event, $user);

        abort_unless($event->attendee_types()->exists(), 422, 'No hay una toma de asistencia iniciada.');
        abort_unless($event->attendance_closed, 422, 'La toma de asistencia no está cerrada.');

        $event->update(['attendance_closed' => false]);

        return $event->fresh(['attendee_types']);
    }
}
