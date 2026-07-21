<?php

namespace App\Actions\Events;

use App\Models\EventTeamRole;
use App\Support\ChurchEventsAccess;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteEventTeamRoleAction
{
    use AsAction;

    public function handle(int $event_team_role_id): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('events.team_roles.delete'), 403);

        $role = EventTeamRole::query()
            ->forAuthUser()
            ->findOrFail($event_team_role_id);

        abort_unless($role->canDelete(), 403);

        $role->delete();
    }
}
