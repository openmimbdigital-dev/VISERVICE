<?php

namespace App\Livewire\Admin\Settings\Events\AttendeeTypes;

use App\Actions\Settings\Events\DeleteAttendeeTypeAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Concerns\ResolvesCatalogDatatableRowPermissions;
use App\Models\AttendeeType;
use App\Support\ChurchEventsAccess;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatatableAttendeeTypes extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;
    use ResolvesCatalogDatatableRowPermissions;

    public bool $exportable = true;

    public ?int $perPage = 25;

    public function builder(): Builder
    {
        ChurchEventsAccess::authorize();

        return AttendeeType::query()
            ->visibleToUser()
            ->select('attendee_types.*')
            ->leftJoin('businesses', 'attendee_types.business_id', '=', 'businesses.id')
            ->orderBy('attendee_types.minimum_range');
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('attendee_types.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),
            Column::callback(
                ['attendee_types.minimum_range', 'attendee_types.maximum_range'],
                fn ($minimum, $maximum) => e("{$minimum} – {$maximum}")
            )->label('Rango')->unsortable(),
            Column::callback(['attendee_types.description'], function ($description) {
                return $description
                    ? '<span class="text-sm text-slate-600">' . e(str($description)->limit(80)) . '</span>'
                    : '<span class="text-slate-400">—</span>';
            })->label('Descripción')->unsortable(),
            Column::callback(['attendee_types.general'], function ($general) {
                return $general
                    ? '<span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">Sí</span>'
                    : '<span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">No</span>';
            })->label('General')->filterable([1 => 'Sí', 0 => 'No']),
        ];

        if (auth()->user()->hasRole('superAdmin')) {
            $columns[] = Column::callback(['businesses.name'], function ($business_name) {
                return $business_name ? e($business_name) : '<span class="text-slate-400">—</span>';
            })->label('Negocio');
        }

        $columns[] = Column::callback(
            ['attendee_types.id', 'attendee_types.general', 'attendee_types.business_id'],
            function ($id, $general, $business_id) {
                $permissions = $this->catalogRowPermissions(
                    (bool) $general,
                    $business_id,
                    0,
                    'settings.attendee_types.edit',
                    'settings.attendee_types.delete',
                );

                return view('livewire.admin.settings.events.attendee-types.actions', [
                    'id'                  => $id,
                    'can_edit'            => $permissions['can_edit'],
                    'can_delete'          => $permissions['can_delete'],
                    'is_general_readonly' => $permissions['is_general_readonly'],
                ]);
            }
        )->label('Acciones')->unsortable();

        return $columns;
    }

    public function deleteRecord(int $id): void
    {
        ChurchEventsAccess::authorize();
        abort_unless(auth()->user()->can('settings.attendee_types.delete'), 403);
        $this->askDeleteConfirmation($id, '¿Eliminar este tipo de asistente?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteAttendeeTypeAction::run($this->delete_id);
            $this->alertDeleteSuccess('Tipo de asistente eliminado correctamente.');
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el tipo de asistente.');
        }
    }
}
