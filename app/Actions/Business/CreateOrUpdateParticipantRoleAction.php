<?php

namespace App\Actions\Business;

use App\Actions\LogUserHistoricalAction;
use App\Models\ParticipantRole;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateParticipantRoleAction
{
    use AsAction;

    /** @param  array{name: string, description: ?string, active: bool}  $data */
    public function handle(int $business_id, ?int $participant_role_id, array $data): ParticipantRole
    {
        $user = auth()->user();

        abort_unless(
            $user->can($participant_role_id ? 'participants.roles.edit' : 'participants.roles.create'),
            403
        );

        if (! $user->hasRole('superAdmin')) {
            abort_unless($user->belongsToBusiness($business_id), 403);
        }

        $attributes = [
            ...$data,
            'business_id' => $business_id,
        ];

        if ($participant_role_id) {
            $role = ParticipantRole::query()
                ->forAuthUser($user)
                ->findOrFail($participant_role_id);

            abort_unless((int) $role->business_id === (int) $business_id, 403);

            if ($role->hasDependencies()) {
                abort(422, 'No se puede editar: el rol está asignado a uno o más participantes.');
            }

            $role->update($attributes);
            $role = $role->fresh();

            LogUserHistoricalAction::run(
                action: 'updated',
                module: 'participants.roles',
                description: "Actualizó el rol de participante {$role->name}",
                subject: $role,
                subject_label: $role->name,
                properties: [
                    'active' => (bool) $role->active,
                ],
                business_id: $business_id,
            );

            return $role;
        }

        $role = ParticipantRole::query()->create($attributes);

        LogUserHistoricalAction::run(
            action: 'created',
            module: 'participants.roles',
            description: "Creó el rol de participante {$role->name}",
            subject: $role,
            subject_label: $role->name,
            properties: [
                'active' => (bool) $role->active,
            ],
            business_id: $business_id,
        );

        return $role;
    }
}
