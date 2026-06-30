<?php

namespace App\Livewire\Admin\Settings\Equipment\Attributes;

use App\Actions\Settings\Equipment\DeleteAttributeAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Attribute;
use App\Models\EquipmentType;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class DatatableAttributes extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;

    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('attribute-deleted')]
    public function onAttributeDeleted(): void {}

    public function builder(): Builder
    {
        $equipment_type_names = DB::table('attribute_equipment_types')
            ->join('equipment_types', function ($join) {
                $join->on('equipment_types.id', '=', 'attribute_equipment_types.model_id')
                    ->whereNull('equipment_types.deleted_at');
            })
            ->where('attribute_equipment_types.model_type', EquipmentType::class)
            ->whereNull('attribute_equipment_types.deleted_at')
            ->select(
                'attribute_equipment_types.attribute_id',
                DB::raw("GROUP_CONCAT(DISTINCT equipment_types.name ORDER BY equipment_types.name SEPARATOR ', ') as assigned_equipment_type_names")
            )
            ->groupBy('attribute_equipment_types.attribute_id');

        return Attribute::query()
            ->forAuthUser()
            ->select('attributes.*')
            ->leftJoinSub(
                $equipment_type_names,
                'type_assignments',
                fn ($join) => $join->on('attributes.id', '=', 'type_assignments.attribute_id')
            )
            ->addSelect('type_assignments.assigned_equipment_type_names')
            ->orderByDesc('attributes.created_at');
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::name('attributes.name')
                ->label('Nombre')
                ->searchable()
                ->sortable(),

            Column::callback(['attributes.type'], function ($type) {
                return e($this->typeLabel((string) $type));
            })->label('Tipo'),

            Column::callback(['type_assignments.assigned_equipment_type_names'], function ($names) {
                if (! $names) {
                    return '<span class="text-slate-400">—</span>';
                }

                return '<span class="text-slate-700">' . e($names) . '</span>';
            })->label('Tipos de equipo')->unsortable(),

            Column::callback(['attributes.general'], function ($general) {
                if ($general) {
                    return '<span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 ring-1 ring-indigo-600/20">Sí</span>';
                }

                return '<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-500/20">No</span>';
            })->label('General')->filterable([1 => 'Sí', 0 => 'No']),
        ];

        if (auth()->user()->hasRole('superAdmin')) {
            $columns[] = Column::callback(
                ['attributes.id', 'attributes.general'],
                function ($id, $general) {
                    if ($general) {
                        return '<span class="text-slate-400">Todos</span>';
                    }

                    return e($this->businessNamesForAttribute((int) $id));
                },
                [],
                'businesses'
            )->label('Comercios')->unsortable();
        }

        return array_merge($columns, [
            Column::callback(['attributes.required'], function ($required) {
                $label = $required ? 'Sí' : 'No';

                return '<span class="text-sm text-slate-700">' . $label . '</span>';
            })->label('Obligatorio')->filterable([1 => 'Sí', 0 => 'No']),

            Column::callback(
                ['attributes.id', 'attributes.general'],
                function ($id, $general) {
                    $permissions = $this->attributeRowPermissions((bool) $general);

                    return view('livewire.admin.settings.equipment.attributes.actions', [
                        'id'                  => $id,
                        'can_edit'            => $permissions['can_edit'],
                        'can_delete'          => $permissions['can_delete'],
                        'is_general_readonly' => $permissions['is_general_readonly'],
                    ]);
                },
                [],
                'actions'
            )->label('Acciones')->unsortable(),
        ]);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()->can('settings.attributes.delete'), 403);

        $attribute = Attribute::query()->forAuthUser()->findOrFail($id);
        abort_unless($attribute->isEditableBy(), 403);

        $this->askDeleteConfirmation($id, '¿Estás seguro de querer eliminar este atributo?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteAttributeAction::run($this->delete_id);

            $this->alertDeleteSuccess('Atributo eliminado correctamente.');
            $this->dispatch('attribute-deleted');
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el atributo.');
        }
    }

    /**
     * @return array{can_edit: bool, can_delete: bool, is_general_readonly: bool}
     */
    protected function attributeRowPermissions(bool $general): array
    {
        $user            = auth()->user();
        $is_super_admin  = $user?->hasRole('superAdmin');
        $is_general_readonly = $general && ! $is_super_admin;

        if (! $user?->can('settings.attributes.edit') && ! $user?->can('settings.attributes.delete')) {
            return [
                'can_edit'            => false,
                'can_delete'          => false,
                'is_general_readonly' => $is_general_readonly,
            ];
        }

        if ($is_super_admin) {
            return [
                'can_edit'            => $user->can('settings.attributes.edit'),
                'can_delete'          => $user->can('settings.attributes.delete'),
                'is_general_readonly' => false,
            ];
        }

        $can_manage = ! $general;

        return [
            'can_edit'            => $user->can('settings.attributes.edit') && $can_manage,
            'can_delete'          => $user->can('settings.attributes.delete') && $can_manage,
            'is_general_readonly' => $is_general_readonly,
        ];
    }

    protected function businessNamesForAttribute(int $attribute_id): string
    {
        $names = Attribute::query()
            ->with('businesses:id,name')
            ->find($attribute_id)
            ?->businesses
            ->pluck('name')
            ->join(', ');

        return $names ?: '—';
    }

    protected function typeLabel(string $type): string
    {
        return match ($type) {
            'text'     => 'Texto',
            'number'   => 'Número',
            'textarea' => 'Área de texto',
            'select'   => 'Lista desplegable',
            'radio'    => 'Botones de radio',
            'checkbox' => 'Casillas de verificación',
            'color'    => 'Color',
            default    => $type,
        };
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
