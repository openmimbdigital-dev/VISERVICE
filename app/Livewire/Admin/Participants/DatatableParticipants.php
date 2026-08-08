<?php

namespace App\Livewire\Admin\Participants;

use App\Actions\Business\DeleteParticipantAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Participant;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class DatatableParticipants extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;

    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('participant-deleted')]
    public function onParticipantDeleted(): void {}

    public function builder(): Builder
    {
        $memberships_count = DB::table('event_team_members')
            ->select('participant_id', DB::raw('COUNT(*) as memberships_count'))
            ->whereNull('deleted_at')
            ->groupBy('participant_id');

        $query = Participant::query()
            ->forAuthUser()
            ->leftJoin('participant_roles', 'participants.participant_role_id', '=', 'participant_roles.id')
            ->leftJoinSub(
                $memberships_count,
                'participant_memberships',
                fn ($join) => $join->on('participants.id', '=', 'participant_memberships.participant_id')
            )
            ->select('participants.*')
            ->addSelect('participant_roles.name as role_name')
            ->addSelect('participant_memberships.memberships_count');

        if (auth()->user()->hasRole('superAdmin')) {
            return $query
                ->leftJoin('businesses', 'participants.business_id', '=', 'businesses.id')
                ->addSelect('businesses.name as business_name')
                ->orderBy('businesses.name')
                ->orderBy('participants.last_name')
                ->orderBy('participants.first_name');
        }

        return $query
            ->orderBy('participants.last_name')
            ->orderBy('participants.first_name');
    }

    public function getColumns(): Model|array
    {
        $columns = [];

        if (auth()->user()->hasRole('superAdmin')) {
            $columns[] = Column::raw('businesses.name AS business_name')
                ->label('Negocio')
                ->sortable()
                ->searchable();
        }

        return array_merge($columns, [
            Column::callback(['participants.first_name', 'participants.last_name'], function ($first, $last) {
                return e(trim(($first ?? '').' '.($last ?? '')) ?: '—');
            })->label('Nombre')->searchable(),

            Column::raw('participant_roles.name AS role_name')
                ->label('Rol')
                ->sortable()
                ->searchable(),

            Column::callback(['participants.document_type', 'participants.document_number'], function ($type, $number) {
                if (! $type && ! $number) {
                    return '—';
                }

                return '<span class="font-mono text-xs">'.e($type ?: '—').': '.e($number ?: '—').'</span>';
            })->label('Documento'),

            Column::name('participants.phone_number')
                ->label('Teléfono')
                ->searchable(),

            Column::name('participants.email')
                ->label('Email')
                ->searchable(),

            Column::callback(['participants.status'], function ($status) {
                $label = $status ? 'Activo' : 'Inactivo';
                $class = $status
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium '.$class.'">'.$label.'</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(['participants.id', 'participant_memberships.memberships_count'], function ($id, $memberships_count) {
                return view('livewire.admin.participants.actions', [
                    'id' => $id,
                    'has_dependencies' => (int) $memberships_count > 0,
                ]);
            })->label('Acciones')->unsortable(),
        ]);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('participants.delete'), 403);

        $participant = Participant::query()->forAuthUser()->findOrFail($id);

        if ($participant->hasDependencies()) {
            $this->dispatch('swal', [
                'title' => 'No se puede eliminar',
                'text' => 'El participante está siendo utilizado en otras referencias.',
                'icon' => 'warning',
            ]);

            return;
        }

        $this->askDeleteConfirmation($id, '¿Eliminar este participante?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteParticipantAction::run($this->delete_id);

            $this->alertDeleteSuccess('Participante eliminado correctamente.');
            $this->dispatch('participant-deleted');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->alertDeleteWarning($e->getMessage() ?: 'No se pudo eliminar el participante.');
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el participante.');
        }
    }

    public function render()
    {
        $this->dispatch('refreshDynamic');

        if ($this->persistPerPage) {
            session()->put([$this->sessionStorageKey().'_perpage' => $this->perPage]);
        }

        return view('datatables::datatable');
    }
}
