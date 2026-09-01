<?php

namespace App\Actions\Settings\Events;

use App\Models\EventCategory;
use App\Support\ChurchEventsAccess;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateEventCategoryAction
{
    use AsAction;

    /** @param array{name: string, description: ?string, type: string} $data */
    public function handle(?int $event_category_id, array $data): EventCategory
    {
        $user = auth()->user();

        ChurchEventsAccess::authorize($user);
        abort_unless(
            $user->can($event_category_id ? 'settings.event_categories.edit' : 'settings.event_categories.create'),
            403
        );

        $is_super_admin = $user->hasRole('superAdmin');
        $attributes = [
            ...$data,
            'business_id' => $is_super_admin ? null : $user->business_id,
            'general'     => $is_super_admin,
        ];

        if ($event_category_id) {
            $event_category = EventCategory::query()
                ->visibleToUser($user)
                ->findOrFail($event_category_id);

            abort_unless($event_category->isEditableBy($user), 403);
            $event_category->update($attributes);

            return $event_category->fresh();
        }

        return EventCategory::query()->create($attributes);
    }
}
