<?php

namespace App\Actions\Events;

use App\Actions\LogUserHistoricalAction;
use App\Enums\EventCategoryType;
use App\Enums\Weekday;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventTeam;
use App\Support\EventsAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePeriodicEventsAction
{
    use AsAction;

    /**
     * @param  array{
     *     event_category_id: int,
     *     name: string,
     *     description: ?string,
     *     start_time: string,
     *     end_time: string,
     *     active: bool,
     *     attendance_enabled: bool,
     *     participation_enabled: bool,
     *     schedule_mode: string,
     *     year: int,
     *     start_month?: int,
     *     end_month?: int,
     *     weekdays?: list<int>,
     *     specific_month?: int,
     *     specific_dates?: list<string>,
     *     event_team_ids?: list<int>
     * }  $data
     * @return Collection<int, Event>
     */
    public function handle(int $business_id, array $data): Collection
    {
        $user = auth()->user();

        EventsAccess::authorizeCreateEvents($user);

        if (! $user->hasRole('superAdmin')) {
            abort_unless((int) $business_id === (int) $user->business_id, 403);
        }

        $category = EventCategory::query()
            ->visibleToUser($user)
            ->findOrFail($data['event_category_id']);

        abort_unless($category->type === EventCategoryType::Periodic, 422);

        $dates = $this->resolveDates($data);

        // Días de la semana: omitir fechas pasadas y crear solo las ≥ hoy.
        if (($data['schedule_mode'] ?? '') === 'weekdays') {
            $dates = $this->rejectPastDates($dates);
        }

        if ($dates === []) {
            $field = ($data['schedule_mode'] ?? '') === 'specific_dates'
                ? 'form.specific_dates'
                : 'form.weekdays';

            throw ValidationException::withMessages([
                $field => 'No hay fechas válidas a partir de hoy con la selección indicada.',
            ]);
        }

        $team_ids = EventTeam::query()
            ->where('business_id', $business_id)
            ->whereIn('id', $data['event_team_ids'] ?? [])
            ->pluck('id')
            ->all();

        return DB::transaction(function () use ($business_id, $data, $dates, $team_ids) {
            $events = collect();

            foreach ($dates as $date) {
                $event = Event::query()->create([
                    'business_id' => $business_id,
                    'event_category_id' => $data['event_category_id'],
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'date_start' => $date,
                    'date_end' => $date,
                    'day' => Weekday::labelFromDate($date),
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'active' => (bool) ($data['active'] ?? true),
                    'attendance_enabled' => $data['attendance_enabled'],
                    'participation_enabled' => $data['participation_enabled'],
                    'participation_closed' => true,
                ]);

                $event->teams()->sync($team_ids);
                $events->push($event);
            }

            $first = $events->first();
            $count = $events->count();

            LogUserHistoricalAction::run(
                action: 'created',
                module: 'events.events',
                description: "Creó {$count} eventos periódicos «{$data['name']}»",
                subject: $first,
                subject_label: $data['name'],
                properties: [
                    'count' => $count,
                    'schedule_mode' => $data['schedule_mode'],
                    'year' => (int) $data['year'],
                    'start_month' => $data['start_month'] ?? null,
                    'end_month' => $data['end_month'] ?? null,
                    'weekdays' => $data['weekdays'] ?? [],
                    'specific_month' => $data['specific_month'] ?? null,
                    'specific_dates' => $data['specific_dates'] ?? [],
                    'event_category_id' => $data['event_category_id'],
                ],
                business_id: $business_id,
            );

            return $events;
        });
    }

    /**
     * @param  array{
     *     schedule_mode: string,
     *     year: int,
     *     start_month?: int,
     *     end_month?: int,
     *     weekdays?: list<int>,
     *     specific_dates?: list<string>
     * }  $data
     * @return list<string>
     */
    private function resolveDates(array $data): array
    {
        if ($data['schedule_mode'] === 'specific_dates') {
            $dates = array_values(array_unique(array_map('strval', $data['specific_dates'] ?? [])));
            sort($dates);

            return $dates;
        }

        return CalculatePeriodicEventDatesAction::run(
            (int) $data['year'],
            (int) $data['start_month'],
            (int) $data['end_month'],
            $data['weekdays'] ?? []
        );
    }

    /**
     * @param  list<string>  $dates
     * @return list<string>
     */
    private function rejectPastDates(array $dates): array
    {
        $today = Carbon::today()->toDateString();

        return array_values(array_filter(
            $dates,
            fn (string $date) => $date >= $today
        ));
    }
}
