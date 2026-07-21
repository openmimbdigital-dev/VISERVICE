<?php

namespace App\Livewire\Admin\Events\TeamRoles;

use App\Actions\Events\DeleteEventTeamRoleAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\EventTeamRole;
use App\Support\ChurchEventsAccess;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatatableEventTeamRoles extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;

    public bool $exportable = true;

    public ?int $perPage = 25;

    public function builder(): Builder
    {
        ChurchEventsAccess::authorize();

        return EventTeamRole::query()
            ->forAuthUser()
            ->select('event_team_roles.*')
            ->leftJoin('businesses', 'event_team_roles.business_id', '=', 'businesses.id')
            ->orderBy('event_team_roles.name');
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('event_team_roles.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),
            Column::callback(['event_team_roles.functions'], function ($functions) {
                return $functions
                    ? '<span class="text-sm text-slate-600">'.e(str($functions)->limit(80)).'</span>'
                    : '<span class="text-slate-400">—</span>';
            })->label('Funciones')->unsortable(),
            Column::callback(['event_team_roles.active'], function ($active) {
                return $active
                    ? '<span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20">Activo</span>'
                    : '<span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">Inactivo</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),
            Column::raw('(SELECT COUNT(*) FROM event_team_role WHERE event_team_role.event_team_role_id = event_team_roles.id) AS teams_count')
                ->label('Equipos')
                ->sortable(),
            Column::raw('(SELECT COUNT(*) FROM event_team_members WHERE event_team_members.event_team_role_id = event_team_roles.id AND event_team_members.deleted_at IS NULL) AS members_count')
                ->label('Asignaciones')
                ->sortable(),
        ];

        if (auth()->user()->hasRole('superAdmin')) {
            $columns[] = Column::callback(['businesses.name'], function ($business_name) {
                return $business_name ? e($business_name) : '<span class="text-slate-400">—</span>';
            })->label('Iglesia');
        }

        $columns[] = Column::callback(
            ['event_team_roles.id', 'event_team_roles.business_id'],
            function ($id, $business_id) {
                $user = auth()->user();
                $belongs = $user->hasRole('superAdmin') || $user->belongsToBusiness((int) $business_id);
                $can_edit = $user?->can('events.team_roles.edit') && $belongs;
                $can_delete = $user?->can('events.team_roles.delete') && $belongs;

                return view('livewire.admin.events.team-roles.actions', [
                    'id' => $id,
                    'can_edit' => $can_edit,
                    'can_delete' => $can_delete,
                ]);
            }
        )->label('Acciones')->unsortable();

        return $columns;
    }

    public function deleteRecord(int $id): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()->can('events.team_roles.delete'), 403);
        $this->askDeleteConfirmation($id, '¿Eliminar este rol del equipo?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteEventTeamRoleAction::run($this->delete_id);
            $this->alertDeleteSuccess('Rol del equipo eliminado correctamente.');
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el rol. Puede estar en uso por un equipo.');
        }
    }
}
