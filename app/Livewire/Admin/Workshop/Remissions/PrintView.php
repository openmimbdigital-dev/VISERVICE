<?php

namespace App\Livewire\Admin\Workshop\Remissions;

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
            'equipments',
            'workOrder.items.productType',
            'workOrder.items.equipment',
            'workOrder.items.catalogProduct',
            'workOrder.associatedDocuments',
            'createdBy',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.workshop.remissions.print')
            ->layoutData([
                'pdfUrl'  => route('admin.workshop.remissions.pdf', $this->remission),
                'backUrl' => route('admin.workshop.remissions.show', $this->remission),
            ]);
    }
}
