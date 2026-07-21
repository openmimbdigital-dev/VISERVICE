<?php

namespace App\Actions\Settings\Events;

use App\Models\EventCategory;
use App\Support\ChurchEventsAccess;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteEventCategoryAction
{
    use AsAction;

    public function handle(int $event_category_id): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('settings.event_categories.delete'), 403);

        $event_category = EventCategory::query()
            ->visibleToUser()
            ->findOrFail($event_category_id);

        abort_unless($event_category->canDelete(), 403);
        $event_category->delete();
    }
}
