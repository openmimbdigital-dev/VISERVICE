<?php

namespace App\Livewire\Admin\Workshop\Invoices;

use App\Models\WorkOrderInvoice;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Factura')]
class Show extends Component
{
    public WorkOrderInvoice $invoice;

    public function mount(WorkOrderInvoice $workOrderInvoice): void
    {
        abort_unless(auth()->user()?->can('workshop.invoices.view'), 403);

        abort_unless(
            WorkOrderInvoice::query()->forAuthUser()->whereKey($workOrderInvoice->id)->exists(),
            404
        );

        $this->invoice = $workOrderInvoice->load([
            'items.workOrderItem.productType',
            'items.workOrderItem.equipment',
            'workOrder.client',
            'createdBy',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.workshop.invoices.show');
    }
}
