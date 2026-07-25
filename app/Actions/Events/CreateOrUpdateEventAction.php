<?php

namespace App\Actions\Events;

use App\Models\Event;
use App\Models\EventCategory;
use App\Support\ChurchEventsAccess;
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
     *     end_time: string
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

        if ($event_id) {
            $event = Event::query()
                ->forAuthUser($user)
                ->findOrFail($event_id);

            abort_unless((int) $event->business_id === (int) $business_id, 403);

            $event->update([
                'event_category_id' => $data['event_category_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'date' => $data['date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
            ]);

            return $event->fresh(['category', 'business']);
        }

        return Event::query()->create([
            'business_id' => $business_id,
            'event_category_id' => $data['event_category_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'date' => $data['date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
        ])->load(['category', 'business']);
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
