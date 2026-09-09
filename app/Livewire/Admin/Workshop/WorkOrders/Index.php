<?php

namespace App\Livewire\Admin\Workshop\WorkOrders;

use App\Enums\WorkOrderStatus;
use App\Models\Client;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Órdenes de Trabajo')]
class Index extends Component
{
    /** Estado de la OT; cadena vacía = todos. */
    public string $status = '';

    public ?int $client_id = null;

    public string $date_from = '';

    public string $date_to = '';

    /** '' | 'overdue' | 'week' — sobre la fecha de entrega estimada. */
    public string $delivery = '';

    /** '' | 'pending' | 'invoiced' */
    public string $billing = '';

    public bool $show_filters = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.view'), 403);
    }

    #[On('work-order-deleted')]
    public function onRecordDeleted(): void {}

    /** Filtra por estado desde las tarjetas; volver a pulsar el mismo lo quita. */
    public function filterByStatus(string $status): void
    {
        $this->status = $this->status === $status ? '' : $status;
    }

    public function resetFilters(): void
    {
        $this->status = '';
        $this->client_id = null;
        $this->date_from = '';
        $this->date_to = '';
        $this->delivery = '';
        $this->billing = '';
    }

    /**
     * Si el usuario invierte el rango, se corrige solo en vez de devolver vacío.
     */
    public function updatedDateFrom(): void
    {
        if ($this->date_to !== '' && $this->date_from !== '' && $this->date_from > $this->date_to) {
            $this->date_to = $this->date_from;
        }
    }

    public function updatedDateTo(): void
    {
        if ($this->date_from !== '' && $this->date_to !== '' && $this->date_to < $this->date_from) {
            $this->date_from = $this->date_to;
        }
    }

    public function render()
    {
        // Los contadores por estado respetan el resto de filtros, para que las
        // tarjetas digan cuántas OT hay dentro de lo que se está mirando.
        $counted = $this->applyFilters(WorkOrder::query()->forAuthUser(), skip_status: true);

        $stats = [
            'borradores'  => (clone $counted)->where('status', WorkOrderStatus::Draft)->count(),
            'creadas'     => (clone $counted)->where('status', WorkOrderStatus::Created)->count(),
            'en_proceso'  => (clone $counted)->where('status', WorkOrderStatus::InProgress)->count(),
            'finalizadas' => (clone $counted)->where('status', WorkOrderStatus::Completed)->count(),
            'canceladas'  => (clone $counted)->where('status', WorkOrderStatus::Cancelled)->count(),
        ];

        $clients = Client::query()
            ->forAuthUser()
            ->whereHas('workOrders')
            ->orderBy('name')
            ->get(['id', 'name']);

        // El total del encabezado sí incluye el estado: es lo que muestra la grilla.
        $total_filtered = $this->status !== ''
            ? (clone $counted)->where('status', $this->status)->count()
            : array_sum($stats);

        return view('livewire.admin.workshop.work-orders.index', [
            'stats'                => $stats,
            'total_filtered'       => $total_filtered,
            'clients'              => $clients,
            'active_filters'       => $this->activeFilterCount(),
            'delivery_options'     => [
                ''        => 'Cualquier entrega',
                'overdue' => 'Entrega vencida',
                'week'    => 'Entrega en 7 días',
                'none'    => 'Sin fecha de entrega',
            ],
            'billing_options'      => [
                ''         => 'Facturada o no',
                'pending'  => 'Sin facturar',
                'invoiced' => 'Ya facturada',
            ],
            'filter_key'           => $this->filterKey(),
        ]);
    }

    private function activeFilterCount(): int
    {
        return count(array_filter([
            $this->status !== '',
            $this->client_id !== null,
            $this->date_from !== '',
            $this->date_to !== '',
            $this->delivery !== '',
            $this->billing !== '',
        ]));
    }

    /** Identidad de los filtros: al cambiar, la datatable se remonta. */
    private function filterKey(): string
    {
        return implode('|', [
            $this->status ?: 'all',
            $this->client_id ?: 'all',
            $this->date_from ?: 'x',
            $this->date_to ?: 'x',
            $this->delivery ?: 'x',
            $this->billing ?: 'x',
        ]);
    }

    /**
     * Aplica los filtros al query. Es la misma lógica que usa la datatable, y
     * vive aquí para que los contadores y el listado nunca se contradigan.
     */
    public function applyFilters(Builder $query, bool $skip_status = false): Builder
    {
        return self::applyFiltersTo($query, [
            'status'    => $skip_status ? '' : $this->status,
            'client_id' => $this->client_id,
            'date_from' => $this->date_from,
            'date_to'   => $this->date_to,
            'delivery'  => $this->delivery,
            'billing'   => $this->billing,
        ]);
    }

    /**
     * @param  array{status?:string, client_id?:int|null, date_from?:string, date_to?:string, delivery?:string, billing?:string}  $filters
     */
    public static function applyFiltersTo(Builder $query, array $filters): Builder
    {
        $table = $query->getModel()->getTable();

        if (($filters['status'] ?? '') !== '') {
            $query->where("{$table}.status", $filters['status']);
        }

        if (! empty($filters['client_id'])) {
            $query->where("{$table}.client_id", (int) $filters['client_id']);
        }

        if (($filters['date_from'] ?? '') !== '') {
            $query->whereDate("{$table}.created_at", '>=', $filters['date_from']);
        }

        if (($filters['date_to'] ?? '') !== '') {
            $query->whereDate("{$table}.created_at", '<=', $filters['date_to']);
        }

        match ($filters['delivery'] ?? '') {
            'overdue' => $query->whereNotNull("{$table}.estimated_delivery")
                ->whereDate("{$table}.estimated_delivery", '<', now()->toDateString())
                ->whereIn("{$table}.status", WorkOrderStatus::openValues()),
            'week' => $query->whereNotNull("{$table}.estimated_delivery")
                ->whereBetween("{$table}.estimated_delivery", [
                    now()->toDateString(),
                    now()->addDays(7)->toDateString(),
                ]),
            'none' => $query->whereNull("{$table}.estimated_delivery"),
            default => null,
        };

        // «Facturada» significa con factura viva: una anulada deja la OT por facturar.
        $has_invoice = fn ($sub) => $sub->selectRaw('1')
            ->from('work_order_invoices')
            ->whereColumn('work_order_invoices.work_order_id', "{$table}.id")
            ->whereNull('work_order_invoices.deleted_at')
            ->whereIn('work_order_invoices.status', ['pendiente', 'pagada', 'vencida']);

        match ($filters['billing'] ?? '') {
            'invoiced' => $query->whereExists($has_invoice),
            'pending' => $query->whereNotExists($has_invoice),
            default => null,
        };

        return $query;
    }
}
