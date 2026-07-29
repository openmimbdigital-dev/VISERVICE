<?php

namespace App\Livewire\Admin\Events\Manage;

use App\Actions\Events\DeleteEventAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Event;
use App\Support\EventsAccess;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\DateColumn;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DatatableEvents extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;

    public bool $exportable = true;

    public ?int $perPage = 25;

    public ?int $event_category_id = null;

    public ?string $date = null;

    public ?int $month = null;

    public function builder(): Builder
    {
        EventsAccess::authorizeViewEvents();

        $query = Event::query()
            ->forAuthUser()
            ->whereNull('events.parent_id')
            ->select('events.*')
            ->leftJoin('businesses', 'events.business_id', '=', 'businesses.id')
            ->orderBy('events.date_start')
            ->orderBy('events.start_time');

        if ($this->event_category_id) {
            $query->where('events.event_category_id', $this->event_category_id);
        }

        if ($this->date) {
            $query->whereDate('events.date_start', '<=', $this->date)
                ->whereDate('events.date_end', '>=', $this->date);
        }

        if ($this->month !== null && $this->month >= 1 && $this->month <= 12) {
            $query->where(function ($month_query) {
                $month_query
                    ->whereMonth('events.date_start', $this->month)
                    ->orWhereMonth('events.date_end', $this->month);
            });
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
            DateColumn::name('events.date_start')
                ->label('Inicio')
                ->format('d/m/Y')
                ->sortable()
                ->searchable(),
            DateColumn::name('events.date_end')
                ->label('Fin')
                ->format('d/m/Y')
                ->sortable()
                ->searchable(),
            Column::raw("DATE_FORMAT(events.date_start, '%d/%m/%Y') AS event_date_start_formatted")
                ->label('Inicio formateado')
                ->searchable()
                ->hide(),
            Column::raw("DATE_FORMAT(events.date_end, '%d/%m/%Y') AS event_date_end_formatted")
                ->label('Fin formateado')
                ->searchable()
                ->hide(),
            Column::name('events.day')
                ->label('Día')
                ->searchable()
                ->sortable(),
            Column::callback(['events.active'], function ($active) {
                return $active
                    ? '<span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-600/20">Activo</span>'
                    : '<span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-500/15">Inactivo</span>';
            })->label('Estado')->unsortable(),
            Column::callback(['events.start_time', 'events.end_time'], function ($start, $end) {
                return e($this->formatTimeAmPm($start).' – '.$this->formatTimeAmPm($end));
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
                $belongs_to_business = EventsAccess::belongsToBusiness((int) $business_id, $user);
                $can_edit = $belongs_to_business && $user?->can('events.events.edit');
                $can_delete = $belongs_to_business && $user?->can('events.events.delete');

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
        $event = Event::query()->forAuthUser()->findOrFail($id);

        EventsAccess::authorizeDeleteEvent($event);

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

    private function formatTimeAmPm(mixed $time): string
    {
        if ($time === null || $time === '') {
            return '—';
        }

        try {
            return Carbon::parse((string) $time)
                ->locale('es')
                ->isoFormat('h:mm a');
        } catch (\Throwable) {
            return '—';
        }
    }
}
