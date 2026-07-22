<?php

namespace App\Livewire\Admin\Events\Teams;

use App\Actions\Events\DeleteEventTeamAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\EventTeam;
use App\Support\ChurchEventsAccess;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatatableEventTeams extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;

    public bool $exportable = true;

    public ?int $perPage = 25;

    public function builder(): Builder
    {
        ChurchEventsAccess::authorize();

        $query = EventTeam::query()
            ->forAuthUser()
            ->select('event_teams.*')
            ->leftJoin('businesses', 'event_teams.business_id', '=', 'businesses.id')
            ->orderByDesc('event_teams.created_at');

        return $query;
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('event_teams.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),
            Column::callback(['event_teams.description'], function ($description) {
                return $description
                    ? '<span class="text-sm text-slate-600">'.e(str($description)->limit(80)).'</span>'
                    : '<span class="text-slate-400">—</span>';
            })->label('Descripción')->unsortable(),
            Column::callback(['event_teams.active'], function ($active) {
                return $active
                    ? '<span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20">Activo</span>'
                    : '<span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">Inactivo</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),
        ];

        if (auth()->user()->hasRole('superAdmin')) {
            $columns[] = Column::callback(['businesses.name'], function ($business_name) {
                return $business_name ? e($business_name) : '<span class="text-slate-400">—</span>';
            })->label('Iglesia');
        }

        $columns[] = Column::callback(
            ['event_teams.id', 'event_teams.business_id'],
            function ($id, $business_id) {
                $user = auth()->user();
                $can_edit = $user?->can('events.teams.edit')
                    && ($user->hasRole('superAdmin') || $user->belongsToBusiness((int) $business_id));
                $can_delete = $user?->can('events.teams.delete')
                    && ($user->hasRole('superAdmin') || $user->belongsToBusiness((int) $business_id));

                return view('livewire.admin.events.teams.actions', [
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
        abort_unless(auth()->user()->can('events.teams.delete'), 403);
        $this->askDeleteConfirmation($id, '¿Eliminar este equipo de evento?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteEventTeamAction::run($this->delete_id);
            $this->alertDeleteSuccess('Equipo de evento eliminado correctamente.');
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el equipo de evento.');
        }
    }
}
