<?php

namespace App\Livewire\Admin\Workshop\Quotations;

use App\Actions\AcceptQuotationAction;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\ServiceCatalog;
use App\Models\SparePartCatalog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cotización')]
class Show extends Component
{
    public Quotation $quotation;

    public function mount(Quotation $quotation): void
    {
        abort_unless(auth()->user()?->can('workshop.quotations.view'), 403);

        $this->quotation = $quotation;
    }

    // Modal item
    public bool   $showItemModal = false;
    public ?int   $editing_item_id = null;
    public string $item_type           = 'servicio';
    public string $item_description    = '';
    public string $item_quantity       = '1';
    public string $item_unit_price     = '0';
    public string $item_discount       = '0';
    public ?int   $catalog_item_id     = null;

    // Modal rechazo
    public bool   $showRejectModal     = false;
    public string $reject_reason       = '';

    protected function rules(): array
    {
        return [
            'item_type'        => 'required|in:servicio,repuesto,otro',
            'item_description' => 'required|string|max:200',
            'item_quantity'    => 'required|numeric|min:0.01',
            'item_unit_price'  => 'required|numeric|min:0',
            'item_discount'    => 'required|numeric|min:0|max:100',
        ];
    }

    // ── Items ────────────────────────────────────────────────────────────────

    public function openAddItem(): void
    {
        $this->resetItemForm();
        $this->showItemModal = true;
    }

    public function openEditItem(int $id): void
    {
        $item = QuotationItem::findOrFail($id);
        $this->editing_item_id    = $item->id;
        $this->item_type          = $item->item_type;
        $this->item_description   = $item->description;
        $this->item_quantity      = $item->quantity;
        $this->item_unit_price    = $item->unit_price;
        $this->item_discount      = $item->discount_percentage;
        $this->catalog_item_id    = $item->catalog_item_id;
        $this->showItemModal      = true;
    }

    public function fillFromCatalog(int $catalogId, string $type): void
    {
        $this->catalog_item_id = $catalogId;
        if ($type === 'servicio') {
            $s = ServiceCatalog::find($catalogId);
            if ($s) {
                $this->item_description = $s->name;
                $this->item_unit_price  = $s->default_price;
                $this->item_type        = 'servicio';
            }
        } else {
            $p = SparePartCatalog::find($catalogId);
            if ($p) {
                $this->item_description = $p->name;
                $this->item_unit_price  = $p->unit_price;
                $this->item_type        = 'repuesto';
            }
        }
    }

    public function saveItem(): void
    {
        $this->validateOnly('item_type,item_description,item_quantity,item_unit_price,item_discount');

        $qty      = (float) $this->item_quantity;
        $price    = (float) $this->item_unit_price;
        $discount = (float) $this->item_discount;
        $subtotal = round(($qty * $price) * (1 - $discount / 100), 2);

        $data = [
            'item_type'           => $this->item_type,
            'description'         => $this->item_description,
            'quantity'            => $qty,
            'unit_price'          => $price,
            'discount_percentage' => $discount,
            'subtotal'            => $subtotal,
            'catalog_item_id'     => $this->catalog_item_id,
            'catalog_item_type'   => $this->catalog_item_id
                ? ($this->item_type === 'servicio' ? 'services_catalog' : 'spare_parts_catalog')
                : null,
        ];

        if ($this->editing_item_id) {
            QuotationItem::findOrFail($this->editing_item_id)->update($data);
        } else {
            $this->quotation->items()->create($data);
        }

        $this->quotation->recalculateTotals();
        $this->quotation->refresh();
        $this->closeItemModal();
    }

    public function deleteItem(int $id): void
    {
        QuotationItem::findOrFail($id)->delete();
        $this->quotation->recalculateTotals();
        $this->quotation->refresh();
    }

    public function closeItemModal(): void
    {
        $this->showItemModal = false;
        $this->resetItemForm();
    }

    private function resetItemForm(): void
    {
        $this->editing_item_id  = null;
        $this->item_type        = 'servicio';
        $this->item_description = '';
        $this->item_quantity    = '1';
        $this->item_unit_price  = '0';
        $this->item_discount    = '0';
        $this->catalog_item_id  = null;
        $this->resetValidation();
    }

    // ── Status changes ───────────────────────────────────────────────────────

    public function sendQuotation(): void
    {
        $this->quotation->update(['status' => 'enviada', 'sent_at' => now()]);
        $this->quotation->refresh();
        $this->dispatch('swal', ['title' => 'Cotización enviada al cliente', 'icon' => 'success']);
    }

    public function acceptQuotation(): void
    {
        $workOrder = AcceptQuotationAction::run($this->quotation);
        $this->dispatch('swal', ['title' => "OT {$workOrder->reference} creada", 'icon' => 'success']);
        $this->redirectRoute('admin.workshop.work-orders.show', $workOrder->id, navigate: true);
    }

    public function openRejectModal(): void
    {
        $this->reject_reason  = '';
        $this->showRejectModal = true;
    }

    public function rejectQuotation(): void
    {
        $this->quotation->update([
            'status'      => 'rechazada',
            'rejected_at' => now(),
            'observations'=> $this->reject_reason ?: null,
        ]);
        $this->quotation->refresh();
        $this->showRejectModal = false;
        $this->dispatch('swal', ['title' => 'Cotización rechazada', 'icon' => 'warning']);
    }

    public function updateTaxPercentage(string $value): void
    {
        $this->quotation->update(['tax_percentage' => $value]);
        $this->quotation->recalculateTotals();
        $this->quotation->refresh();
    }

    public function render()
    {
        $business_id = auth()->user()->business_id;

        $services    = ServiceCatalog::where('business_id', $business_id)->active()->orderBy('name')->get();
        $spare_parts = SparePartCatalog::where('business_id', $business_id)->active()->orderBy('name')->get();

        return view('livewire.admin.workshop.quotations.show',
            compact('services', 'spare_parts'));
    }
}
