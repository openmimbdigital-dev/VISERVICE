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
            'business', 'client', 'equipments', 'quotationServiceType',
            'paymentMethod', 'bankAccount', 'createdBy',
            'items.productType', 'items.productCategory', 'items.catalogProduct', 'items.equipment',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.workshop.quotations.print', [
            'category_subtotals' => $this->quotation->subtotalsByPdfCategory(),
        ])->layoutData([
            'pdfUrl'  => route('admin.workshop.quotations.pdf', $this->quotation),
            'backUrl' => route('admin.workshop.quotations.show', $this->quotation),
        ]);
    }
}
