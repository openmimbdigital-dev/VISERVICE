<?php

namespace App\Actions\Events;

use App\Actions\LogUserHistoricalAction;
use App\Models\EventTeam;
use App\Models\EventTeamMember;
use App\Models\EventTeamRole;
use App\Models\User;
use App\Support\ChurchEventsAccess;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateEventTeamAction
{
    use AsAction;

    /**
     * @param  array{
     *     name: string,
     *     description: ?string,
     *     active: bool,
     *     role_ids: list<int>,
     *     members: list<array{user_id: int, event_team_role_id: int}>
     * }  $data
     */
    public function handle(int $business_id, ?int $event_team_id, array $data): EventTeam
    {
        $user = auth()->user();

        ChurchEventsAccess::authorize($user);
        abort_unless(
            $user->can($event_team_id ? 'events.teams.edit' : 'events.teams.create'),
            403
        );

        if (! $user->hasRole('superAdmin')) {
            abort_unless((int) $business_id === (int) $user->business_id, 403);
        }

        $role_ids = array_values(array_unique(array_map('intval', $data['role_ids'])));
        $this->assertRolesBelongToBusiness($business_id, $role_ids);
        $this->assertMembersAreValid($business_id, $role_ids, $data['members']);

        return DB::transaction(function () use ($business_id, $event_team_id, $data, $role_ids, $user) {
            $is_editing = (bool) $event_team_id;

            if ($event_team_id) {
                $event_team = EventTeam::query()
                    ->forAuthUser($user)
                    ->findOrFail($event_team_id);

                abort_unless((int) $event_team->business_id === (int) $business_id, 403);

                $event_team->update([
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'active' => $data['active'],
                ]);
            } else {
                $event_team = EventTeam::query()->create([
                    'business_id' => $business_id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'active' => $data['active'],
                ]);
            }

            $event_team->roles()->sync($role_ids);
            $this->syncMembers($event_team, $business_id, $data['members']);

            $event_team = $event_team->fresh(['roles', 'members']);

            LogUserHistoricalAction::run(
                action: $is_editing ? 'updated' : 'created',
                module: 'events.teams',
                description: ($is_editing ? 'Actualizó' : 'Creó')." el equipo de evento {$event_team->name}",
                subject: $event_team,
                subject_label: $event_team->name,
                properties: [
                    'active' => (bool) $event_team->active,
                    'roles_count' => $event_team->roles->count(),
                    'members_count' => $event_team->members->count(),
                ],
                business_id: $business_id,
            );

            return $event_team;
        });
    }

    /** @param  list<int>  $role_ids */
    private function assertRolesBelongToBusiness(int $business_id, array $role_ids): void
    {
        if ($role_ids === []) {
            return;
        }

        $count = EventTeamRole::query()
            ->where('business_id', $business_id)
            ->whereIn('id', $role_ids)
            ->count();

        abort_unless($count === count($role_ids), 422);
    }

    /**
     * @param  list<int>  $role_ids
     * @param  list<array{user_id: int, event_team_role_id: int}>  $members
     */
    private function assertMembersAreValid(int $business_id, array $role_ids, array $members): void
    {
        foreach ($members as $member) {
            abort_unless(in_array((int) $member['event_team_role_id'], $role_ids, true), 422);

            $user_belongs = User::query()
                ->whereKey($member['user_id'])
                ->whereHas('businesses', fn ($query) => $query->whereKey($business_id))
                ->exists();

            abort_unless($user_belongs, 422);
        }
    }

    /**
     * @param  list<array{user_id: int, event_team_role_id: int}>  $members
     */
    private function syncMembers(EventTeam $event_team, int $business_id, array $members): void
    {
        $keep_keys = [];

        foreach ($members as $member) {
            $record = EventTeamMember::withTrashed()->updateOrCreate(
                [
                    'event_team_id' => $event_team->id,
                    'event_team_role_id' => $member['event_team_role_id'],
                    'user_id' => $member['user_id'],
                ],
                [
                    'business_id' => $business_id,
                    'deleted_at' => null,
                ]
            );

            $keep_keys[] = $record->id;
        }

        EventTeamMember::query()
            ->where('event_team_id', $event_team->id)
            ->when($keep_keys !== [], fn ($query) => $query->whereNotIn('id', $keep_keys))
            ->when($keep_keys === [], fn ($query) => $query)
            ->get()
            ->each
            ->delete();
    }
}
