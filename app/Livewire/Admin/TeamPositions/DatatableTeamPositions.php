<?php

namespace App\Livewire\Admin\TeamPositions;

use App\Actions\Business\DeleteTeamPositionAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Concerns\ResolvesCatalogDatatableRowPermissions;
use App\Models\TeamPosition;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class DatatableTeamPositions extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;
    use ResolvesCatalogDatatableRowPermissions;

    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('team-position-saved')]
    public function onTeamPositionSaved(): void {}

    public function builder(): Builder
    {
        $users_count = DB::table('users')
            ->select('team_position_id', DB::raw('COUNT(*) as users_count'))
            ->whereNull('deleted_at')
            ->whereNotNull('team_position_id')
            ->groupBy('team_position_id');

        $query = TeamPosition::query()
            ->visibleToUser()
            ->leftJoin('business_types', 'team_positions.business_type_id', '=', 'business_types.id')
            ->leftJoinSub(
                $users_count,
                'position_users',
                fn ($join) => $join->on('team_positions.id', '=', 'position_users.team_position_id')
            )
            ->select('team_positions.*')
            ->addSelect('business_types.name as business_type_name')
            ->addSelect('position_users.users_count')
            ->orderByDesc('team_positions.created_at');

        if (auth()->user()->hasRole('superAdmin')) {
            $query->leftJoin('businesses', 'team_positions.business_id', '=', 'businesses.id')
                ->addSelect('businesses.name as business_name');
        }

        return $query;
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('team_positions.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),

            Column::raw('business_types.name AS business_type_name')
                ->label('Tipo de negocio')
                ->searchable()
                ->sortable(),

            Column::callback(['team_positions.general'], function ($general) {
                if ($general) {
                    return '<span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">Sí</span>';
                }

                return '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">No</span>';
            })->label('General')->filterable([1 => 'Sí', 0 => 'No']),
        ];

        if (auth()->user()->hasRole('superAdmin')) {
            $columns[] = Column::raw('businesses.name AS business_name')
                ->label('Negocio')
                ->searchable()
                ->sortable();
        }

        return array_merge($columns, [
            Column::callback(['team_positions.active'], function ($active) {
                $label = $active ? 'Activo' : 'Inactivo';
                $class = $active
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' . $class . '">' . $label . '</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(['position_users.users_count'], function ($count) {
                return (int) $count;
            })->label('Usuarios'),

            Column::callback(
                ['team_positions.id', 'team_positions.general', 'team_positions.business_id', 'position_users.users_count'],
                function ($id, $general, $business_id, $users_count) {
                    $permissions = $this->catalogRowPermissions(
                        (bool) $general,
                        $business_id,
                        (int) $users_count,
                        'team_positions.edit',
                        'team_positions.delete',
                    );

                    return view('livewire.admin.team-positions.actions', [
                        'id'                  => $id,
                        'can_edit'            => $permissions['can_edit'],
                        'can_delete'          => $permissions['can_delete'],
                        'is_general_readonly' => $permissions['is_general_readonly'],
                        'users_count'         => (int) $users_count,
                    ]);
                }
            )->label('Acciones')->unsortable(),
        ]);
    }

    public function openEditEvent(int $id): void
    {
        $this->dispatch('open-team-position-edit', id: $id);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('team_positions.delete'), 403);

        $this->askDeleteConfirmation($id, '¿Eliminar este cargo del equipo?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteTeamPositionAction::run($this->delete_id);

            $this->alertDeleteSuccess('Cargo eliminado correctamente.');
            $this->dispatch('team-position-deleted');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->alertDeleteWarning($e->getMessage() ?: 'No se pudo eliminar el cargo.');
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el cargo.');
        }
    }

    public function render()
    {
        $this->dispatch('refreshDynamic');

        if ($this->persistPerPage) {
            session()->put([$this->sessionStorageKey() . '_perpage' => $this->perPage]);
        }

        return view('datatables::datatable');
    }
}
