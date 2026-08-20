<?php

namespace App\Livewire\Admin\Workshop\WorkOrders;

use App\Models\GeneralConfig;
use App\Models\WorkOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.print')]
#[Title('Imprimir orden de trabajo')]
class PrintView extends Component
{
    public WorkOrder $workOrder;

    public function mount(WorkOrder $workOrder): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.view'), 403);

        abort_unless(
            WorkOrder::query()->forAuthUser()->whereKey($workOrder->id)->exists(),
            404
        );

        $this->workOrder = $workOrder->load([
            'business',
            'client',
            'equipments',
            'quotation',
            'createdBy',
            'items.productType',
            'items.catalogProduct',
            'items.equipment',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.workshop.work-orders.print', $this->documentClientData())
            ->layoutData([
                'pdfUrl'  => route('admin.workshop.work-orders.pdf', $this->workOrder),
                'backUrl' => route('admin.workshop.work-orders.show', $this->workOrder),
            ]);
    }

    private function documentClientData(): array
    {
        $document_client = $this->workOrder->document_client ?? [];
        $document_labels = [];

        if ($document_client !== []) {
            $document_labels = GeneralConfig::query()
                ->forAuthUser()
                ->associatedDocumentsOt()
                ->where('business_id', $this->workOrder->business_id)
                ->whereIn('label', array_keys($document_client))
                ->pluck('value', 'label')
                ->all();
        }

        return [
            'document_client' => $document_client,
            'document_labels' => $document_labels,
        ];
    }
}
