<?php

namespace App\Actions\Events;

use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\Participant;
use App\Support\EventsAccess;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class SetEventParticipantAttendanceAction
{
    use AsAction;

    public function handle(int $event_id, int $participant_id, bool $attended): EventAttendance
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);
        EventsAccess::authorizeViewSchedule($user);

        $event = Event::query()
            ->forAuthUser($user)
            ->where('multi_day', false)
            ->findOrFail($event_id);

        $state = $event->participationCaptureState();
        abort_unless($state['available'], 403, $state['message'] ?? 'La toma de participación no está disponible.');
        abort_unless(! $event->participation_closed, 403, 'La toma de participación está cerrada.');

        $participant = Participant::query()
            ->forAuthUser($user)
            ->where('business_id', $event->business_id)
            ->where('status', true)
            ->findOrFail($participant_id);

        abort_unless($event->date_start !== null, 422, 'El evento no tiene fecha definida.');

        $date_event = $event->date_start->toDateString();

        return DB::transaction(function () use ($event, $participant, $attended, $date_event) {
            $record = EventAttendance::withTrashed()
                ->where('event_id', $event->id)
                ->where('attendable_type', Participant::class)
                ->where('attendable_id', $participant->id)
                ->whereDate('date_event', $date_event)
                ->first();

            if ($record === null) {
                return EventAttendance::query()->create([
                    'event_id' => $event->id,
                    'attendable_type' => Participant::class,
                    'attendable_id' => $participant->id,
                    'date_event' => $date_event,
                    'attendance_hour' => $attended ? now()->format('H:i:s') : null,
                    'attendance' => $attended,
                ]);
            }

            if ($record->trashed()) {
                $record->restore();
            }

            $record->update([
                'attendance' => $attended,
                'attendance_hour' => $attended
                    ? now()->format('H:i:s')
                    : $record->attendance_hour,
            ]);

            return $record->fresh();
        });
    }
}
