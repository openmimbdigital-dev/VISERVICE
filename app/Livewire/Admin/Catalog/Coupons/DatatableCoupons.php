<?php

namespace App\Livewire\Admin\Catalog\Coupons;

use App\Actions\Catalog\DeleteCouponAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\Coupon;
use Arm092\LivewireDatatables\Column;
use Arm092\LivewireDatatables\Livewire\LivewireDatatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;

class DatatableCoupons extends LivewireDatatable
{
    use ConfirmsDeletionWithLivewireAlert;

    public bool $exportable = true;

    public ?int $perPage = 25;

    #[On('coupon-deleted')]
    public function onCouponDeleted(): void {}

    #[On('coupon-saved')]
    public function onCouponSaved(): void {}

    public function builder(): Builder
    {
        // Los usos se cuentan con un join: el listado tiene que poder ordenarse
        // y filtrarse por ellos, y una relación cargada no sirve para eso.
        $uses = DB::table('work_orders')
            ->select('coupon_id', DB::raw('COUNT(*) as uses_count'))
            ->whereNotNull('coupon_id')
            ->whereNull('deleted_at')
            ->groupBy('coupon_id');

        $query = Coupon::query()
            ->forAuthUser()
            ->select('coupons.*')
            ->leftJoinSub($uses, 'coupon_usage', fn ($join) => $join->on('coupons.id', '=', 'coupon_usage.coupon_id'))
            ->addSelect('coupon_usage.uses_count')
            ->orderByDesc('coupons.created_at');

        if (auth()->user()->hasRole('superAdmin')) {
            $query->leftJoin('businesses', 'coupons.business_id', '=', 'businesses.id');
        }

        return $query;
    }

    public function getColumns(): Model|array
    {
        $columns = [
            Column::callback(['coupons.code'], fn ($code) => '<span class="font-mono text-xs font-semibold tracking-wide text-slate-800">'.e($code).'</span>')
                ->label('Código')
                ->searchable()
                ->sortable(),

            Column::name('coupons.name')->label('Nombre')->searchable()->sortable(),

            Column::callback(['coupons.discount_type', 'coupons.discount_value'], function ($type, $value) {
                $label = $type === Coupon::DISCOUNT_PERCENTAGE
                    ? rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.').'%'
                    : col_money($value);

                return '<span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-600/20">−'.e($label).'</span>';
            })->label('Descuento')->filterable([
                Coupon::DISCOUNT_PERCENTAGE => 'Porcentaje',
                Coupon::DISCOUNT_AMOUNT     => 'Valor fijo',
            ]),

            Column::callback(['coupons.starts_at', 'coupons.ends_at'], function ($starts_at, $ends_at) {
                if (! $starts_at && ! $ends_at) {
                    return '<span class="text-slate-400">Sin límite</span>';
                }

                $from = $starts_at ? \Carbon\Carbon::parse($starts_at)->format('d/m/Y') : '—';
                $to   = $ends_at ? \Carbon\Carbon::parse($ends_at)->format('d/m/Y') : '—';

                return '<span class="text-xs text-slate-600">'.$from.' → '.$to.'</span>';
            })->label('Vigencia'),

            Column::callback(['coupon_usage.uses_count', 'coupons.max_uses'], function ($uses, $max) {
                $uses = (int) $uses;

                return '<span class="text-xs text-slate-600">'.$uses.' / '.($max === null ? '∞' : (int) $max).'</span>';
            })->label('Usos'),
        ];

        if (auth()->user()->hasRole('superAdmin')) {
            $columns[] = Column::callback(['businesses.name'], fn ($name) => $name ? e($name) : '<span class="text-slate-400">—</span>')
                ->label('Negocio');
        }

        return array_merge($columns, [
            Column::callback(['coupons.active'], function ($active) {
                $label = $active ? 'Activo' : 'Inactivo';
                $class = $active
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                    : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

                return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium '.$class.'">'.$label.'</span>';
            })->label('Estado')->filterable([1 => 'Activo', 0 => 'Inactivo']),

            Column::callback(['coupons.id', 'coupon_usage.uses_count'], function ($id, $uses) {
                return view('livewire.admin.catalog.coupons.actions', [
                    'id'         => $id,
                    'can_edit'   => auth()->user()->can('catalog.coupons.edit'),
                    // Un cupón ya usado solo se puede desactivar, no borrar.
                    'can_delete' => auth()->user()->can('catalog.coupons.delete') && (int) $uses === 0,
                ]);
            })->label('Acciones')->unsortable(),
        ]);
    }

    public function openEditEvent(int $id): void
    {
        $this->dispatch('open-coupon-edit', id: $id);
    }

    public function deleteRecord(int $id): void
    {
        abort_unless(auth()->user()?->can('catalog.coupons.delete'), 403);

        $this->askDeleteConfirmation($id, '¿Eliminar este cupón de descuento?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteCouponAction::run($this->delete_id);

            $this->alertDeleteSuccess('Cupón eliminado correctamente.');
            $this->dispatch('coupon-deleted');
        } catch (ValidationException $exception) {
            $this->alertDeleteWarning(collect($exception->errors())->flatten()->first());
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el cupón.');
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
