<?php

namespace App\Livewire\Admin\Workshop\Quotations;

use App\Models\Quotation;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.print')]
#[Title('Imprimir cotización')]
class PrintView extends Component
{
    public Quotation $quotation;

    public function mount(Quotation $quotation): void
    {
        abort_unless(auth()->user()?->can('workshop.quotations.view'), 403);

        $this->quotation = $quotation->load([
            'business', 'client', 'equipment', 'quotationServiceType',
            'paymentMethod', 'bankAccount', 'createdBy',
            'items.itemType', 'items.itemCategory', 'items.catalogItem',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.workshop.quotations.print', [
            'category_subtotals' => $this->quotation->subtotalsByPdfCategory(),
        ]);
    }
}
