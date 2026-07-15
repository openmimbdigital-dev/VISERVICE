<?php

namespace App\Livewire\Admin\Workshop\WorkOrders;

use App\Models\Product;
use App\Models\ProductType;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Orden de Trabajo')]
class Show extends Component
{
    public WorkOrder $workOrder;

    public bool $showItemModal = false;

    public ?int $editing_item_id = null;

    public ?int $product_type_id = null;

    public ?int $product_id = null;

    public string $item_description = '';

    public string $item_quantity = '1';

    public string $item_unit_price = '0';

    public string $item_discount = '0';

    public string $item_status = 'pendiente';

    public string $item_notes = '';

    public function mount(WorkOrder $workOrder): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.view'), 403);

        $this->workOrder = $workOrder;
    }

    public function openAddItem(): void
    {
        $this->resetItemForm();
        $this->showItemModal = true;
    }

    public function openEditItem(int $id): void
    {
        $item = WorkOrderItem::query()
            ->where('work_order_id', $this->workOrder->id)
            ->findOrFail($id);

        $this->editing_item_id = $item->id;
        $this->product_type_id = $item->product_type_id;
        $this->product_id = $item->product_id;
        $this->item_description = $item->description;
        $this->item_quantity = (string) $item->quantity;
        $this->item_unit_price = (string) $item->unit_price;
        $this->item_discount = (string) $item->discount_percentage;
        $this->item_status = $item->status;
        $this->item_notes = $item->technician_notes ?? '';
        $this->showItemModal = true;
    }

    public function updatedProductTypeId(): void
    {
        $this->product_id = null;
    }

    public function updatedProductId(?int $value): void
    {
        if (! $value) {
            return;
        }

        $product = Product::query()
            ->forAuthUser()
            ->where('business_id', $this->workOrder->business_id)
            ->find($value);

        if (! $product) {
            return;
        }

        $this->product_type_id = $product->product_type_id;
        $this->item_description = $product->name;
        $this->item_unit_price = (string) $product->sale_price;
    }

    public function saveItem(): void
    {
        $this->validate([
            'item_description' => 'required|string|max:255',
            'product_type_id'  => 'nullable|integer|exists:product_types,id',
            'product_id'       => 'nullable|integer|exists:products,id',
            'item_quantity'    => 'required|numeric|min:0.01',
            'item_unit_price'  => 'required|numeric|min:0',
            'item_discount'    => 'nullable|numeric|min:0|max:100',
        ]);

        $qty = (float) $this->item_quantity;
        $price = (float) $this->item_unit_price;
        $discount = (float) $this->item_discount;
        $subtotal = round(($qty * $price) * (1 - $discount / 100), 2);

        $data = [
            'product_id'          => $this->product_id ?: null,
            'product_type_id'     => $this->product_type_id ?: null,
            'description'         => $this->item_description,
            'quantity'            => $qty,
            'unit_price'          => $price,
            'discount_percentage' => $discount,
            'subtotal'            => $subtotal,
            'status'              => $this->item_status,
            'technician_notes'    => $this->item_notes ?: null,
        ];

        if ($this->editing_item_id) {
            WorkOrderItem::query()
                ->where('work_order_id', $this->workOrder->id)
                ->whereKey($this->editing_item_id)
                ->firstOrFail()
                ->update($data);
        } else {
            $this->workOrder->items()->create($data);
        }

        $this->workOrder->recalculateTotals();
        $this->workOrder->refresh();
        $this->closeItemModal();
    }

    public function deleteItem(int $id): void
    {
        WorkOrderItem::query()
            ->where('work_order_id', $this->workOrder->id)
            ->whereKey($id)
            ->firstOrFail()
            ->delete();

        $this->workOrder->recalculateTotals();
        $this->workOrder->refresh();
    }

    public function updateItemStatus(int $id, string $status): void
    {
        WorkOrderItem::query()
            ->where('work_order_id', $this->workOrder->id)
            ->whereKey($id)
            ->firstOrFail()
            ->update(['status' => $status]);

        $this->workOrder->refresh();
    }

    public function closeItemModal(): void
    {
        $this->showItemModal = false;
        $this->resetItemForm();
    }

    private function resetItemForm(): void
    {
        $this->editing_item_id = null;
        $this->product_type_id = null;
        $this->product_id = null;
        $this->item_description = '';
        $this->item_notes = '';
        $this->item_quantity = '1';
        $this->item_unit_price = '0';
        $this->item_discount = '0';
        $this->item_status = 'pendiente';
        $this->resetValidation();
    }

    public function confirmWorkOrder(): void
    {
        $this->redirectRoute('admin.workshop.work-orders.index', navigate: true);
    }

    public function render()
    {
        $this->workOrder->load([
            'items.productType',
            'items.catalogProduct',
            'client',
            'equipment',
            'quotation',
        ]);

        $product_types = ProductType::query()
            ->visibleToUser()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $catalog_products = Product::query()
            ->forAuthUser()
            ->where('business_id', $this->workOrder->business_id)
            ->where('status', true)
            ->when($this->product_type_id, fn ($q) => $q->where('product_type_id', $this->product_type_id))
            ->orderBy('name')
            ->get();

        return view('livewire.admin.workshop.work-orders.show', compact('product_types', 'catalog_products'));
    }
}
