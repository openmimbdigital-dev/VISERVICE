<?php

namespace App\Actions\Business;

use App\Actions\LogUserHistoricalAction;
use App\Models\ParticipantRole;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteParticipantRoleAction
{
    use AsAction;

    public function handle(int $participant_role_id): void
    {
        abort_unless(auth()->user()?->can('participants.roles.delete'), 403);

        $role = ParticipantRole::query()
            ->forAuthUser()
            ->findOrFail($participant_role_id);

        if ($role->hasDependencies()) {
            abort(422, 'No se puede eliminar: el rol está asignado a uno o más participantes.');
        }

        abort_unless($role->canDelete(), 403);

        LogUserHistoricalAction::run(
            action: 'deleted',
            module: 'participants.roles',
            description: "Eliminó el rol de participante {$role->name}",
            subject: $role,
            subject_label: $role->name,
            properties: [
                'active' => (bool) $role->active,
            ],
            business_id: (int) $role->business_id,
        );

        $role->delete();
    }
}
