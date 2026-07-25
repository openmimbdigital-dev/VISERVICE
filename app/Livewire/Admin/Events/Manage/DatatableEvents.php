<?php

namespace App\Livewire\Admin\Events\Manage;

use App\Actions\Events\DeleteEventAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Event;
use App\Support\ChurchEventsAccess;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatatableEvents extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;

    public bool $exportable = true;

    public ?int $perPage = 25;

    public ?int $event_category_id = null;

    public function builder(): Builder
    {
        ChurchEventsAccess::authorize();

        $query = Event::query()
            ->forAuthUser()
            ->select('events.*')
            ->leftJoin('businesses', 'events.business_id', '=', 'businesses.id')
            ->orderByDesc('events.date')
            ->orderByDesc('events.start_time');

        if ($this->event_category_id) {
            $query->where('events.event_category_id', $this->event_category_id);
        }

        return $query;
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('events.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),
            Column::callback(['events.date'], function ($date) {
                if (! $date) {
                    return '<span class="text-slate-400">—</span>';
                }

                try {
                    return e(\Illuminate\Support\Carbon::parse($date)->format('d/m/Y'));
                } catch (\Throwable) {
                    return e((string) $date);
                }
            })->label('Fecha')->sortable(),
            Column::callback(['events.start_time', 'events.end_time'], function ($start, $end) {
                $start_label = $start ? substr((string) $start, 0, 5) : '—';
                $end_label = $end ? substr((string) $end, 0, 5) : '—';

                return e($start_label.' – '.$end_label);
            })->label('Horario')->unsortable(),
        ];

        if (auth()->user()->hasRole('superAdmin')) {
            $columns[] = Column::callback(['businesses.name'], function ($business_name) {
                return $business_name ? e($business_name) : '<span class="text-slate-400">—</span>';
            })->label('Iglesia');
        }

        $columns[] = Column::callback(
            ['events.id', 'events.business_id', 'events.event_category_id'],
            function ($id, $business_id, $event_category_id) {
                $user = auth()->user();
                $can_edit = $user?->can('events.events.edit')
                    && ($user->hasRole('superAdmin') || $user->belongsToBusiness((int) $business_id));
                $can_delete = $user?->can('events.events.delete')
                    && ($user->hasRole('superAdmin') || $user->belongsToBusiness((int) $business_id));

                return view('livewire.admin.events.manage.actions', [
                    'id' => $id,
                    'event_category_id' => $event_category_id,
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
        abort_unless(auth()->user()->can('events.events.delete'), 403);
        $this->askDeleteConfirmation($id, '¿Eliminar este evento?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteEventAction::run($this->delete_id);
            $this->alertDeleteSuccess('Evento eliminado correctamente.');
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el evento.');
        }
    }
}
