<?php

namespace App\Actions\Events;

use App\Enums\Weekday;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventTeam;
use App\Support\ChurchEventsAccess;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateEventAction
{
    use AsAction;

    /**
     * @param  array{
     *     event_category_id: ?int,
     *     name: string,
     *     description: ?string,
     *     date: string,
     *     start_time: string,
     *     end_time: string,
     *     event_team_ids?: list<int>
     * }  $data
     */
    public function handle(int $business_id, ?int $event_id, array $data): Event
    {
        $user = auth()->user();

        ChurchEventsAccess::authorize($user);
        abort_unless(
            $user->can($event_id ? 'events.events.edit' : 'events.events.create'),
            403
        );

        if (! $user->hasRole('superAdmin')) {
            abort_unless((int) $business_id === (int) $user->business_id, 403);
        }

        $this->assertCategoryIsVisible($data['event_category_id']);

        $payload = [
            'event_category_id' => $data['event_category_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'date' => $data['date'],
            'day' => Weekday::labelFromDate($data['date']),
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
        ];

        $team_ids = $data['event_team_ids'] ?? [];

        return DB::transaction(function () use ($business_id, $event_id, $payload, $team_ids, $user) {
            if ($event_id) {
                $event = Event::query()
                    ->forAuthUser($user)
                    ->findOrFail($event_id);

                abort_unless((int) $event->business_id === (int) $business_id, 403);

                $event->update($payload);
                $this->syncTeams($event, $business_id, $team_ids);

                return $event->fresh(['category', 'business', 'teams']);
            }

            $event = Event::query()->create([
                'business_id' => $business_id,
                ...$payload,
            ]);

            $this->syncTeams($event, $business_id, $team_ids);

            return $event->load(['category', 'business', 'teams']);
        });
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
