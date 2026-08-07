<?php

namespace App\Actions\Events;

use App\Actions\LogUserHistoricalAction;
use App\Enums\Weekday;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventTeam;
use App\Support\EventsAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateMultiDayEventAction
{
    use AsAction;

    /**
     * @param  array{
     *     event_category_id: ?int,
     *     name: string,
     *     description: ?string,
     *     date_start: string,
     *     date_end: string,
     *     start_time: string,
     *     end_time: string,
     *     active: bool,
     *     attendance_enabled: bool,
     *     participation_enabled: bool,
     *     day_schedules: list<array{date: string, start_time: string, end_time: string}>,
     *     event_team_ids?: list<int>
     * }  $data
     */
    public function handle(int $business_id, ?int $event_id, array $data): Event
    {
        $user = auth()->user();

        abort_unless($user !== null, 403);

        if (! $event_id) {
            EventsAccess::authorizeCreateEvents($user);
            $this->assertCreateDatesNotInPast($data['date_start'], $data['date_end']);
        }

        if (! $user->hasRole('superAdmin')) {
            abort_unless((int) $business_id === (int) $user->business_id, 403);
        }

        $this->assertCategoryIsVisible($data['event_category_id']);
        $day_schedules = $this->normalizedDaySchedules($data);
        $first_schedule = $day_schedules[0];
        $last_schedule = $day_schedules[array_key_last($day_schedules)];

        $parent_payload = [
            'event_category_id' => $data['event_category_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'date_start' => $data['date_start'],
            'date_end' => $data['date_end'],
            'day' => Weekday::labelFromDate($data['date_start']),
            'start_time' => $first_schedule['start_time'],
            'end_time' => $last_schedule['end_time'],
            'active' => (bool) ($data['active'] ?? true),
            'multi_day' => true,
            'parent_id' => null,
            'attendance_enabled' => $data['attendance_enabled'],
            'participation_enabled' => $data['participation_enabled'],
        ];

        $team_ids = $data['event_team_ids'] ?? [];

        return DB::transaction(function () use ($business_id, $event_id, $parent_payload, $day_schedules, $team_ids, $user, $data) {
            if ($event_id) {
                $parent = Event::query()
                    ->forAuthUser($user)
                    ->whereNull('parent_id')
                    ->findOrFail($event_id);

                abort_unless((int) $parent->business_id === (int) $business_id, 403);

                if ($parent->hasStartedAttendance()) {
                    throw ValidationException::withMessages([
                        'form.name' => 'No se puede editar: ya se inició la toma de asistencia de este evento.',
                    ]);
                }

                EventsAccess::authorizeEditEvent($parent, $user);

                $parent->update($parent_payload);
                $this->replaceChildren($parent, $business_id, $data, $day_schedules, $team_ids);
                $this->syncTeams($parent, $business_id, $team_ids);

                $parent = $parent->fresh(['category', 'business', 'teams', 'children']);

                LogUserHistoricalAction::run(
                    action: 'updated',
                    module: 'events.events',
                    description: "Actualizó el evento multi-día {$parent->name}",
                    subject: $parent,
                    subject_label: $parent->name,
                    properties: [
                        'date_start' => $parent->date_start?->toDateString(),
                        'date_end' => $parent->date_end?->toDateString(),
                        'multi_day' => true,
                        'children_count' => $parent->children->count(),
                        'event_category_id' => $parent->event_category_id,
                    ],
                    business_id: $business_id,
                );

                return $parent;
            }

            $parent = Event::query()->create([
                'business_id' => $business_id,
                ...$parent_payload,
            ]);

            $this->replaceChildren($parent, $business_id, $data, $day_schedules, $team_ids);
            $this->syncTeams($parent, $business_id, $team_ids);

            $parent = $parent->load(['category', 'business', 'teams', 'children']);

            LogUserHistoricalAction::run(
                action: 'created',
                module: 'events.events',
                description: "Creó el evento multi-día {$parent->name} con {$parent->children->count()} días",
                subject: $parent,
                subject_label: $parent->name,
                properties: [
                    'date_start' => $parent->date_start?->toDateString(),
                    'date_end' => $parent->date_end?->toDateString(),
                    'multi_day' => true,
                    'children_count' => $parent->children->count(),
                    'event_category_id' => $parent->event_category_id,
                ],
                business_id: $business_id,
            );

            return $parent;
        });
    }

    /**
     * @param  array{
     *     date_start: string,
     *     date_end: string,
     *     day_schedules: list<array{date: string, start_time: string, end_time: string}>
     * }  $data
     * @return list<array{date: string, start_time: string, end_time: string}>
     */
    private function normalizedDaySchedules(array $data): array
    {
        $start = Carbon::parse($data['date_start'])->startOfDay();
        $end = Carbon::parse($data['date_end'])->startOfDay();

        if (! $end->gt($start)) {
            throw ValidationException::withMessages([
                'form.date_end' => 'Un evento multi-día requiere que la fecha de fin sea posterior a la de inicio.',
            ]);
        }

        $schedules = collect($data['day_schedules'] ?? [])
            ->map(fn (array $row) => [
                'date' => (string) ($row['date'] ?? ''),
                'start_time' => (string) ($row['start_time'] ?? ''),
                'end_time' => (string) ($row['end_time'] ?? ''),
            ])
            ->filter(fn (array $row) => $row['date'] !== '')
            ->unique('date')
            ->sortBy('date')
            ->values();

        if ($schedules->count() < 2) {
            throw ValidationException::withMessages([
                'form.day_schedules' => 'Debes definir el horario de cada día del rango.',
            ]);
        }

        $expected = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $expected[] = $cursor->toDateString();
            $cursor->addDay();
        }

        $actual = $schedules->pluck('date')->all();

        if ($actual !== $expected) {
            throw ValidationException::withMessages([
                'form.day_schedules' => 'Los horarios por día no coinciden con el rango de fechas seleccionado.',
            ]);
        }

        foreach ($schedules as $index => $row) {
            if ($row['start_time'] === '' || $row['end_time'] === '') {
                throw ValidationException::withMessages([
                    "form.day_schedules.{$index}.start_time" => 'Cada día debe tener hora de inicio y fin.',
                ]);
            }

            if ($row['end_time'] <= $row['start_time']) {
                throw ValidationException::withMessages([
                    "form.day_schedules.{$index}.end_time" => 'La hora de fin debe ser posterior a la de inicio en ese día.',
                ]);
            }
        }

        return $schedules->all();
    }

    /**
     * @param  array{
     *     event_category_id: ?int,
     *     name: string,
     *     description: ?string,
     *     active: bool,
     *     attendance_enabled: bool,
     *     participation_enabled: bool
     * }  $data
     * @param  list<array{date: string, start_time: string, end_time: string}>  $day_schedules
     * @param  list<int>  $team_ids
     */
    private function replaceChildren(
        Event $parent,
        int $business_id,
        array $data,
        array $day_schedules,
        array $team_ids
    ): void {
        $parent->children()->each(function (Event $child) {
            $child->teams()->detach();
            $child->delete();
        });

        foreach ($day_schedules as $schedule) {
            $child = Event::query()->create([
                'business_id' => $business_id,
                'parent_id' => $parent->id,
                'event_category_id' => $data['event_category_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'date_start' => $schedule['date'],
                'date_end' => $schedule['date'],
                'day' => Weekday::labelFromDate($schedule['date']),
                'start_time' => $schedule['start_time'],
                'end_time' => $schedule['end_time'],
                'active' => (bool) ($data['active'] ?? true),
                'multi_day' => false,
                'attendance_enabled' => $data['attendance_enabled'],
                'participation_enabled' => $data['participation_enabled'],
            ]);

            $this->syncTeams($child, $business_id, $team_ids);
        }
    }

    private function assertCreateDatesNotInPast(string $date_start, string $date_end): void
    {
        $today = Carbon::today();
        $start = Carbon::parse($date_start)->startOfDay();
        $end = Carbon::parse($date_end)->startOfDay();

        if ($start->lt($today)) {
            throw ValidationException::withMessages([
                'form.date_start' => 'La fecha de inicio no puede ser anterior al día de hoy.',
            ]);
        }

        if ($end->lt($today)) {
            throw ValidationException::withMessages([
                'form.date_end' => 'La fecha de fin no puede ser anterior al día de hoy.',
            ]);
        }
    }

    /**
     * @param  list<int>  $team_ids
     */
    private function syncTeams(Event $event, int $business_id, array $team_ids): void
    {
        $valid_ids = EventTeam::query()
            ->where('business_id', $business_id)
            ->whereIn('id', $team_ids)
            ->pluck('id')
            ->all();

        $event->teams()->sync($valid_ids);
    }

    private function assertCategoryIsVisible(?int $event_category_id): void
    {
        if ($event_category_id === null) {
            return;
        }

        abort_unless(
            EventCategory::query()
                ->visibleToUser()
                ->whereKey($event_category_id)
                ->exists(),
            422
        );
    }
}
