<?php

namespace App\Livewire\Admin\Workshop\WorkOrders;

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
        $stats = [
            'abiertas'    => WorkOrder::query()->forAuthUser()->where('status', 'abierta')->count(),
            'en_proceso'  => WorkOrder::query()->forAuthUser()->where('status', 'en_proceso')->count(),
            'finalizadas' => WorkOrder::query()->forAuthUser()->where('status', 'finalizada')->count(),
            'canceladas'  => WorkOrder::query()->forAuthUser()->where('status', 'cancelada')->count(),
        ];

        return view('livewire.admin.workshop.work-orders.index', compact('stats'));
    }
}
