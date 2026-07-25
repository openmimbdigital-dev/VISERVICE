<?php

namespace App\Actions\Events;

use App\Enums\EventCategoryType;
use App\Enums\Weekday;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventTeam;
use App\Support\ChurchEventsAccess;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
     *     year: int,
     *     start_month: int,
     *     end_month: int,
     *     weekdays: list<int>,
     *     event_team_ids?: list<int>
     * }  $data
     * @return Collection<int, Event>
     */
    public function handle(int $business_id, array $data): Collection
    {
        $user = auth()->user();

        ChurchEventsAccess::authorize($user);
        abort_unless($user->can('events.events.create'), 403);

        if (! $user->hasRole('superAdmin')) {
            abort_unless((int) $business_id === (int) $user->business_id, 403);
        }

        $category = EventCategory::query()
            ->visibleToUser($user)
            ->findOrFail($data['event_category_id']);

        abort_unless($category->type === EventCategoryType::Periodic, 422);

        $dates = CalculatePeriodicEventDatesAction::run(
            (int) $data['year'],
            (int) $data['start_month'],
            (int) $data['end_month'],
            $data['weekdays']
        );

        if ($dates === []) {
            abort(422, 'No hay fechas que coincidan con el rango y los días seleccionados.');
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
                    'date' => $date,
                    'day' => Weekday::labelFromDate($date),
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                ]);

                $event->teams()->sync($team_ids);
                $events->push($event);
            }

            return $events;
        });
    }
}
