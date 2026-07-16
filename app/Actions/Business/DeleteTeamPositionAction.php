<?php

namespace App\Actions\Business;

use App\Models\TeamPosition;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteTeamPositionAction
{
    use AsAction;

    public function handle(int $team_position_id): void
    {
        abort_unless(auth()->user()?->can('team_positions.delete'), 403);

        $team_position = TeamPosition::query()->visibleToUser()->findOrFail($team_position_id);

        if ($team_position->isGeneralReadonly()) {
            abort(422, 'No se puede eliminar: es un cargo general del sistema.');
        }

        if ($team_position->hasDependencies()) {
            abort(422, 'No se puede eliminar: tiene usuarios asignados.');
        }

        if (! $team_position->canDelete()) {
            abort(403, 'No tienes permiso para eliminar este cargo.');
        }

        $team_position->delete();
    }
}
