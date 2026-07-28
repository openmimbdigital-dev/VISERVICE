<?php

namespace App\Actions\Events;

use App\Actions\LogUserHistoricalAction;
use App\Enums\Weekday;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventTeam;
use App\Support\EventsAccess;
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
     *     date_start: string,
     *     date_end: string,
     *     start_time: string,
     *     end_time: string,
     *     attendance_enabled: bool,
     *     participation_enabled: bool,
     *     event_team_ids?: list<int>
     * }  $data
     */
    public function handle(int $business_id, ?int $event_id, array $data): Event
    {
        $user = auth()->user();

        abort_unless($user !== null, 403);

        if (! $event_id) {
            EventsAccess::authorizeCreateEvents($user);
        }

        if (! $user->hasRole('superAdmin')) {
            abort_unless((int) $business_id === (int) $user->business_id, 403);
        }

        $this->assertCategoryIsVisible($data['event_category_id']);

        $payload = [
            'event_category_id' => $data['event_category_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'date_start' => $data['date_start'],
            'date_end' => $data['date_end'],
            'day' => Weekday::labelFromDate($data['date_start']),
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'attendance_enabled' => $data['attendance_enabled'],
            'participation_enabled' => $data['participation_enabled'],
        ];

        $team_ids = $data['event_team_ids'] ?? [];

        return DB::transaction(function () use ($business_id, $event_id, $payload, $team_ids, $user) {
            if ($event_id) {
                $event = Event::query()
                    ->forAuthUser($user)
                    ->findOrFail($event_id);

                abort_unless((int) $event->business_id === (int) $business_id, 403);

                EventsAccess::authorizeEditEvent($event, $user);

                $event->update($payload);
                $this->syncTeams($event, $business_id, $team_ids);

                $event = $event->fresh(['category', 'business', 'teams']);

                LogUserHistoricalAction::run(
                    action: 'updated',
                    module: 'events.events',
                    description: "Actualizó el evento {$event->name}",
                    subject: $event,
                    subject_label: $event->name,
                    properties: [
                        'date_start' => $event->date_start?->toDateString(),
                        'date_end' => $event->date_end?->toDateString(),
                        'day' => $event->day,
                        'event_category_id' => $event->event_category_id,
                    ],
                    business_id: $business_id,
                );

                return $event;
            }

            $event = Event::query()->create([
                'business_id' => $business_id,
                ...$payload,
            ]);

            $this->syncTeams($event, $business_id, $team_ids);

            $event = $event->load(['category', 'business', 'teams']);

            LogUserHistoricalAction::run(
                action: 'created',
                module: 'events.events',
                description: "Creó el evento {$event->name}",
                subject: $event,
                subject_label: $event->name,
                properties: [
                    'date_start' => $event->date_start?->toDateString(),
                    'date_end' => $event->date_end?->toDateString(),
                    'day' => $event->day,
                    'event_category_id' => $event->event_category_id,
                ],
                business_id: $business_id,
            );

            return $event;
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
