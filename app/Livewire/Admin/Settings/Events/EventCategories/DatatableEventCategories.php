<?php

namespace App\Livewire\Admin\Settings\Events\EventCategories;

use App\Actions\Settings\Events\DeleteEventCategoryAction;
use App\Enums\EventCategoryType;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Concerns\ResolvesCatalogDatatableRowPermissions;
use App\Models\EventCategory;
use App\Support\ChurchEventsAccess;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatatableEventCategories extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;
    use ResolvesCatalogDatatableRowPermissions;

    public bool $exportable = true;

    public ?int $perPage = 25;

    public function builder(): Builder
    {
        ChurchEventsAccess::authorize();

        return EventCategory::query()
            ->visibleToUser()
            ->select('event_categories.*')
            ->leftJoin('businesses', 'event_categories.business_id', '=', 'businesses.id')
            ->orderByDesc('event_categories.created_at');
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('event_categories.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),
            Column::callback(['event_categories.type'], function ($type) {
                return e(EventCategoryType::tryFrom((string) $type)?->label() ?? (string) $type);
            })->label('Tipo')->filterable(EventCategoryType::options()),
            Column::callback(['event_categories.description'], function ($description) {
                return $description
                    ? '<span class="text-sm text-slate-600">' . e(str($description)->limit(80)) . '</span>'
                    : '<span class="text-slate-400">—</span>';
            })->label('Descripción')->unsortable(),
            Column::callback(['event_categories.general'], function ($general) {
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
            ['event_categories.id', 'event_categories.general', 'event_categories.business_id'],
            function ($id, $general, $business_id) {
                $permissions = $this->catalogRowPermissions(
                    (bool) $general,
                    $business_id,
                    0,
                    'settings.event_categories.edit',
                    'settings.event_categories.delete',
                );

                return view('livewire.admin.settings.events.event-categories.actions', [
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
        abort_unless(auth()->user()->can('settings.event_categories.delete'), 403);
        $this->askDeleteConfirmation($id, '¿Eliminar esta categoría de evento?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteEventCategoryAction::run($this->delete_id);
            $this->alertDeleteSuccess('Categoría de evento eliminada correctamente.');
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar la categoría de evento.');
        }
    }
}
