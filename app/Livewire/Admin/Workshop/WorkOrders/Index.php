<?php

namespace App\Livewire\Admin\Workshop\WorkOrders;

use App\Enums\WorkOrderStatus;
use App\Models\WorkOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Órdenes de Trabajo')]
class Index extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.view'), 403);
    }

    #[On('work-order-deleted')]
    public function onRecordDeleted(): void {}

    public function render()
    {
        $base = WorkOrder::query()->forAuthUser();

        $stats = [
            'creadas' => (clone $base)->where('status', WorkOrderStatus::Created)->count(),
            'en_proceso' => (clone $base)->where('status', WorkOrderStatus::InProgress)->count(),
            'finalizadas' => (clone $base)->where('status', WorkOrderStatus::Completed)->count(),
            'canceladas' => (clone $base)->where('status', WorkOrderStatus::Cancelled)->count(),
        ];

        return view('livewire.admin.workshop.work-orders.index', compact('stats'));
    }
}
