<?php

namespace App\Actions\Settings\Events;

use App\Models\AttendeeType;
use App\Support\ChurchEventsAccess;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateAttendeeTypeAction
{
    use AsAction;

    /** @param array{name: string, description: ?string, minimum_range: int, maximum_range: int} $data */
    public function handle(?int $attendee_type_id, array $data): AttendeeType
    {
        $user = auth()->user();

        ChurchEventsAccess::authorize($user);
        abort_unless(
            $user->can($attendee_type_id ? 'settings.attendee_types.edit' : 'settings.attendee_types.create'),
            403
        );

        $is_super_admin = $user->hasRole('superAdmin');
        $attributes = [
            ...$data,
            'business_id' => $is_super_admin ? null : $user->business_id,
            'general'     => $is_super_admin,
        ];

        if ($attendee_type_id) {
            $attendee_type = AttendeeType::query()
                ->visibleToUser($user)
                ->findOrFail($attendee_type_id);

            abort_unless($attendee_type->isEditableBy($user), 403);
            $attendee_type->update($attributes);

            return $attendee_type->fresh();
        }

        return AttendeeType::query()->create($attributes);
    }
}
