<?php

namespace App\Actions\Events;

use App\Actions\LogUserHistoricalAction;
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

        LogUserHistoricalAction::run(
            action: 'deleted',
            module: 'events.teams',
            description: "Eliminó el equipo de evento {$event_team->name}",
            subject: $event_team,
            subject_label: $event_team->name,
            properties: [
                'active' => (bool) $event_team->active,
            ],
            business_id: (int) $event_team->business_id,
        );

        $event_team->delete();
    }
}
