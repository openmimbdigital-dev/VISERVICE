<?php

namespace App\Livewire\Admin\User;

use App\Models\User;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\DateColumn;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatatableUsers extends LivewireDatatable
{
    public bool $exportable = true;

    public ?int $perPage = 25;

    public function builder(): Builder
    {
        $user  = auth()->user();
        $query = User::query()->select('users.*');

        if (! $user->hasRole('superAdmin')) {
            $business_ids = $user->businessIds();

            if ($business_ids === []) {
                return $query->whereRaw('0 = 1');
            }

            $query->whereHas(
                'businesses',
                fn ($q) => $q->whereIn('businesses.id', $business_ids)
            );
        }

        return $query->orderByDesc('users.id');
    }

    public function getColumns(): Model|array
    {
        return [
            Column::name('id')
                ->label('ID')
                ->searchable(),

            Column::name('username')
                ->searchable()
                ->label('Usuario'),

            Column::name('first_name')
                ->searchable()
                ->label('Nombre'),

            Column::name('email')
                ->searchable()
                ->label('Correo'),

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
