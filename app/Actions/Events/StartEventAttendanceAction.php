<?php

namespace App\Actions\Events;

use App\Models\AttendeeType;
use App\Models\Event;
use App\Support\EventsAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class StartEventAttendanceAction
{
    use AsAction;

    /**
     * @param  list<int>  $attendee_type_ids
     */
    public function handle(int $event_id, array $attendee_type_ids): Event
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        $event = Event::query()
            ->forAuthUser($user)
            ->findOrFail($event_id);

        EventsAccess::authorizeStartAttendance($event, $user);

        abort_unless(! $event->attendance_closed, 403, 'La toma de asistencia está cerrada.');

        $state = $event->attendanceCaptureState();
        abort_unless($state['available'], 403, $state['message'] ?? 'La toma de asistencia no está disponible.');

        $attendee_type_ids = array_values(array_unique(array_map('intval', $attendee_type_ids)));

        if ($attendee_type_ids === []) {
            throw ValidationException::withMessages([
                'selected_attendee_type_ids' => 'Selecciona al menos un tipo de asistencia.',
            ]);
        }

        $valid_ids = AttendeeType::query()
            ->whereIn('id', $attendee_type_ids)
            ->where(function ($query) use ($event) {
                $query->where('general', true)
                    ->orWhere('business_id', $event->business_id);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($valid_ids) !== count($attendee_type_ids)) {
            throw ValidationException::withMessages([
                'selected_attendee_type_ids' => 'Uno de los tipos de asistencia seleccionados no es válido.',
            ]);
        }

        return DB::transaction(function () use ($event, $valid_ids) {
            $sync = [];

            foreach ($valid_ids as $attendee_type_id) {
                $sync[$attendee_type_id] = ['attendance' => 0];
            }

            $event->attendee_types()->syncWithoutDetaching($sync);

            return $event->fresh(['attendee_types']);
        });
    }
}
