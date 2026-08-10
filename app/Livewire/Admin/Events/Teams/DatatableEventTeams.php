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
use Illuminate\Support\Facades\DB;

class DatatableEventTeams extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;

    public bool $exportable = true;

    public ?int $perPage = 25;

    public function builder(): Builder
    {
        ChurchEventsAccess::authorize();

        $events_count = DB::table('event_event_team')
            ->join('events', 'events.id', '=', 'event_event_team.event_id')
            ->whereNull('events.deleted_at')
            ->select('event_event_team.event_team_id', DB::raw('COUNT(*) as events_count'))
            ->groupBy('event_event_team.event_team_id');

        return EventTeam::query()
            ->forAuthUser()
            ->select('event_teams.*')
            ->leftJoin('businesses', 'event_teams.business_id', '=', 'businesses.id')
            ->leftJoinSub($events_count, 'team_events', 'team_events.event_team_id', '=', 'event_teams.id')
            ->addSelect('team_events.events_count')
            ->orderByDesc('event_teams.created_at');
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
            ['event_teams.id', 'event_teams.business_id', 'team_events.events_count'],
            function ($id, $business_id, $events_count) {
                $user = auth()->user();
                $belongs = $user->hasRole('superAdmin') || $user->belongsToBusiness((int) $business_id);
                $can_edit = $user?->can('events.teams.edit') && $belongs;
                $can_delete = $user?->can('events.teams.delete') && $belongs;
                $delete_disabled = (int) $events_count > 0;

                return view('livewire.admin.events.teams.actions', [
                    'id' => $id,
                    'can_edit' => $can_edit,
                    'can_delete' => $can_delete,
                    'delete_disabled' => $delete_disabled,
                    'delete_disabled_title' => 'No se puede eliminar: el equipo está asignado a uno o más eventos',
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
            $this->alertDeleteError('No se pudo eliminar el equipo. Puede estar asignado a un evento.');
        }
    }
}
