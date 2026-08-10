<?php

namespace App\Livewire\Admin\Participants\Roles;

use App\Actions\Business\DeleteParticipantRoleAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\ParticipantRole;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DatatableParticipantRoles extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;

    public bool $exportable = true;

    public ?int $perPage = 25;

    public function builder(): Builder
    {
        abort_unless(auth()->user()?->can('participants.roles.view'), 403);

        $participants_count = DB::table('participants')
            ->select('participant_role_id', DB::raw('COUNT(*) as participants_count'))
            ->whereNull('deleted_at')
            ->whereNotNull('participant_role_id')
            ->groupBy('participant_role_id');

        $query = ParticipantRole::query()
            ->forAuthUser()
            ->select('participant_roles.*')
            ->leftJoinSub(
                $participants_count,
                'role_usage',
                fn ($join) => $join->on('participant_roles.id', '=', 'role_usage.participant_role_id')
            )
            ->addSelect('role_usage.participants_count')
            ->orderBy('participant_roles.name');

        if (auth()->user()->hasRole('superAdmin')) {
            return $query
                ->leftJoin('businesses', 'participant_roles.business_id', '=', 'businesses.id')
                ->addSelect('businesses.name as business_name');
        }

        return $query;
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('participant_roles.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),
            Column::callback(['participant_roles.description'], function ($description) {
                return $description
                    ? '<span class="text-sm text-slate-600">'.e(str($description)->limit(80)).'</span>'
                    : '<span class="text-slate-400">—</span>';
            })->label('Descripción')->unsortable(),
            Column::callback(['participant_roles.active'], function ($active) {
                return $active
                    ? '<span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20">Activo</span>'
                    : '<span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">Inactivo</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),
            Column::callback(['role_usage.participants_count'], function ($participants_count) {
                return '<span class="tabular-nums text-slate-700">'.(int) $participants_count.'</span>';
            })->label('Participantes')->unsortable(),
        ];

        if (auth()->user()->hasRole('superAdmin')) {
            $columns[] = Column::raw('businesses.name AS business_name')
                ->label('Negocio')
                ->sortable()
                ->searchable();
        }

        $columns[] = Column::callback(
            ['participant_roles.id', 'participant_roles.business_id', 'role_usage.participants_count'],
            function ($id, $business_id, $participants_count) {
                $user = auth()->user();
                $belongs = $user->hasRole('superAdmin') || $user->belongsToBusiness((int) $business_id);
                $in_use = (int) $participants_count > 0;

                return view('livewire.admin.participants.roles.actions', [
                    'id' => $id,
                    'can_edit' => $user?->can('participants.roles.edit') && $belongs,
                    'can_delete' => $user?->can('participants.roles.delete') && $belongs,
                    'edit_disabled' => $in_use,
                    'delete_disabled' => $in_use,
                    'disabled_title' => 'No se puede modificar: el rol está asignado a uno o más participantes',
                ]);
            }
        )->label('Acciones')->unsortable();

        return $columns;
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('participants.roles.delete'), 403);
        $this->askDeleteConfirmation($id, '¿Eliminar este rol de participante?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteParticipantRoleAction::run($this->delete_id);
            $this->alertDeleteSuccess('Rol de participante eliminado correctamente.');
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el rol. Puede estar asignado a participantes.');
        }
    }
}
