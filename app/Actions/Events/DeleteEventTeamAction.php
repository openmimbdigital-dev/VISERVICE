<?php

namespace App\Actions\Events;

use App\Models\EventTeam;
use App\Support\ChurchEventsAccess;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteEventTeamAction
{
    use AsAction;

    public function handle(int $event_team_id): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()?->can('events.teams.delete'), 403);

        $event_team = EventTeam::query()
            ->forAuthUser()
            ->findOrFail($event_team_id);

        abort_unless($event_team->canDelete(), 403);

        $event_team->delete();
    }
}
