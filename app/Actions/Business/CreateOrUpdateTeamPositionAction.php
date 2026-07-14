<?php

namespace App\Actions\Business;

use App\Models\TeamPosition;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateTeamPositionAction
{
    use AsAction;

    /**
     * @param  array{name: string, active: bool, organization_type_id: int|null}  $data
     */
    public function handle(?int $team_position_id, array $data): TeamPosition
    {
        abort_unless(
            auth()->user()->can($team_position_id ? 'team_positions.edit' : 'team_positions.create'),
            403
        );

        $user           = auth()->user();
        $is_super_admin = $user->hasRole('superAdmin');

        $attributes = [
            'name'   => $data['name'],
            'label'  => TeamPosition::normalizeLabel($data['name']),
            'active' => $data['active'],
        ];

        if ($team_position_id) {
            $team_position = TeamPosition::query()->visibleToUser($user)->findOrFail($team_position_id);
            abort_unless($team_position->isEditableBy($user), 403);

            if ($is_super_admin) {
                $attributes['business_id']           = null;
                $attributes['general']               = true;
                $attributes['organization_type_id']  = $data['organization_type_id'];
            }

            $team_position->update($attributes);

            return $team_position->fresh();
        }

        $attributes['business_id']          = $is_super_admin ? null : $user->business_id;
        $attributes['general']              = $is_super_admin;
        $attributes['organization_type_id'] = $is_super_admin
            ? $data['organization_type_id']
            : ($user->primaryBusiness()?->organization_type_id);

        return TeamPosition::create($attributes);
    }
}
