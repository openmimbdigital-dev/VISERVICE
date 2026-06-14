<?php

namespace App\Livewire\Admin\Roles;

use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\DateColumn;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class DatatableRoles extends LivewireDatatable
{
    public bool $exportable = true;

    public ?int $perPage = 25;

    public function builder(): Builder
    {
        return Role::query()
            ->withCount('permissions')
            ->orderByDesc('id');
    }

    public function getColumns(): Model|array
    {
        return [
            Column::name('id')
                ->label('ID')
                ->searchable(),

            Column::name('name')
                ->searchable()
                ->label('Rol'),


            DateColumn::name('created_at')
                ->label('Registro'),
        ];
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
