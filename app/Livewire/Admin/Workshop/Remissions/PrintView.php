<?php

namespace App\Livewire\Admin\Workshop\Remissions;

use App\Models\GeneralConfig;
use App\Models\Remission;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.print')]
#[Title('Imprimir remisión')]
class PrintView extends Component
{
    public Remission $remission;

    public function mount(Remission $remission): void
    {
        abort_unless(auth()->user()?->can('workshop.remissions.view'), 403);

        abort_unless(
            Remission::query()->forAuthUser()->whereKey($remission->id)->exists(),
            404
        );

        $this->remission = $remission->load([
            'business',
            'client',
            'equipment',
            'workOrder',
            'createdBy',
            'items.productType',
            'items.productCategory',
            'items.unit',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.workshop.remissions.print', $this->documentClientData())
            ->layoutData([
                'pdfUrl'  => route('admin.workshop.remissions.pdf', $this->remission),
                'backUrl' => route('admin.workshop.remissions.show', $this->remission),
            ]);
    }

    private function documentClientData(): array
    {
        $document_client = $this->remission->workOrder?->document_client ?? [];
        $document_labels = [];

        if ($document_client !== []) {
            $document_labels = GeneralConfig::query()
                ->forAuthUser()
                ->associatedDocumentsOt()
                ->where('business_id', $this->remission->business_id)
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
