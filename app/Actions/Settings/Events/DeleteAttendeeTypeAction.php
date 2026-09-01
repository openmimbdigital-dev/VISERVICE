<?php

namespace App\Actions\Settings\Events;

use App\Models\AttendeeType;
use App\Support\ChurchEventsAccess;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteAttendeeTypeAction
{
    use AsAction;

    public function handle(int $attendee_type_id): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('settings.attendee_types.delete'), 403);

        $attendee_type = AttendeeType::query()
            ->visibleToUser()
            ->findOrFail($attendee_type_id);

        abort_unless($attendee_type->canDelete(), 403);
        $attendee_type->delete();
    }
}
