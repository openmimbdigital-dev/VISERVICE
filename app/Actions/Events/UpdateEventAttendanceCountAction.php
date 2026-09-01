<?php

namespace App\Actions\Events;

use App\Models\Event;
use App\Support\EventsAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateEventAttendanceCountAction
{
    use AsAction;

    public function handle(int $event_id, int $attendee_type_id, string $direction): Event
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        abort_unless(in_array($direction, ['increment', 'decrement'], true), 422);

        $event = Event::query()
            ->forAuthUser($user)
            ->findOrFail($event_id);

        EventsAccess::authorizeStartAttendance($event, $user);

        abort_unless(! $event->attendance_closed, 403, 'La toma de asistencia está cerrada.');

        $state = $event->attendanceCaptureState();
        abort_unless($state['available'], 403, $state['message'] ?? 'La toma de asistencia no está disponible.');

        return DB::transaction(function () use ($event, $attendee_type_id, $direction) {
            $pivot = $event->attendee_types()
                ->where('attendee_types.id', $attendee_type_id)
                ->first();

            abort_unless($pivot !== null, 404);

            $current = (int) $pivot->pivot->attendance;

            if ($direction === 'decrement' && $current <= 0) {
                throw ValidationException::withMessages([
                    'attendance' => 'La asistencia no puede ser menor que cero.',
                ]);
            }

            $next = $direction === 'increment' ? $current + 1 : $current - 1;

            $event->attendee_types()->updateExistingPivot($attendee_type_id, [
                'attendance' => $next,
            ]);

            return $event->fresh(['attendee_types']);
        });
    }
}
