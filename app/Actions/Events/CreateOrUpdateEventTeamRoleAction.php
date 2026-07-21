<?php

namespace App\Actions\Events;

use App\Models\EventTeamRole;
use App\Support\ChurchEventsAccess;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateEventTeamRoleAction
{
    use AsAction;

    /** @param  array{name: string, functions: ?string, active: bool}  $data */
    public function handle(int $business_id, ?int $event_team_role_id, array $data): EventTeamRole
    {
        $user = auth()->user();

        ChurchEventsAccess::authorize($user);
        abort_unless(
            $user->can($event_team_role_id ? 'events.team_roles.edit' : 'events.team_roles.create'),
            403
        );

        if (! $user->hasRole('superAdmin')) {
            abort_unless((int) $business_id === (int) $user->business_id, 403);
        }

        $attributes = [
            ...$data,
            'business_id' => $business_id,
        ];

        if ($event_team_role_id) {
            $role = EventTeamRole::query()
                ->forAuthUser($user)
                ->findOrFail($event_team_role_id);

            abort_unless((int) $role->business_id === (int) $business_id, 403);
            $role->update($attributes);

            return $role->fresh();
        }

        return EventTeamRole::query()->create($attributes);
    }
}
