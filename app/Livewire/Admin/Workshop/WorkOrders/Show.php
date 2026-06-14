<?php

namespace App\Livewire\Admin\Workshop\WorkOrders;

use App\Actions\FinalizeWorkOrderAction;
use App\Actions\GenerateWorkOrderInvoiceAction;
use App\Actions\RegisterInvoicePaymentAction;
use App\Models\Remission;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ServiceCatalog;
use App\Models\SparePartCatalog;
use App\Models\WorkOrder;
use App\Models\WorkOrderInvoice;
use App\Models\WorkOrderItem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Orden de Trabajo')]
class Show extends Component
{
    public WorkOrder $workOrder;

    // ── Items ────────────────────────────────────────────────────────────────
    public bool   $showItemModal      = false;
    public ?int   $editing_item_id    = null;
    public string $item_type          = 'servicio';
    public string $item_description   = '';
    public string $item_quantity      = '1';
    public string $item_unit_price    = '0';
    public string $item_discount      = '0';
    public string $item_status        = 'pendiente';
    public string $item_notes         = '';
    public ?int   $catalog_item_id    = null;

    // ── Finalizar ────────────────────────────────────────────────────────────
    public bool   $showFinalizeModal     = false;
    public string $km_exit               = '';
    public string $work_description      = '';

    // ── Remisión ─────────────────────────────────────────────────────────────
    public bool   $showRemissionModal    = false;
    public string $remission_notes       = '';

    // ── Factura ──────────────────────────────────────────────────────────────
    public bool   $showInvoiceModal      = false;
    public string $invoice_due_date      = '';
    public string $invoice_notes         = '';
    // Registrar pago
    public bool   $showPaymentModal      = false;
    public ?int   $paying_invoice_id     = null;
    public string $payment_method        = '';
    public string $payment_reference     = '';
    public string $paid_at               = '';
    public string $payment_notes         = '';

    // ── Orden de Compra ───────────────────────────────────────────────────────
    public bool   $showPOModal           = false;
    public string $po_supplier_name      = '';
    public string $po_supplier_nit       = '';
    public string $po_supplier_phone     = '';
    public string $po_expected_delivery  = '';
    public string $po_notes              = '';
    public array  $po_items              = [];

    public function mount(WorkOrder $workOrder): void
    {
        $this->workOrder = $workOrder;
        $this->km_exit   = $workOrder->km_exit ?? '';
    }

    // ── Items OT ─────────────────────────────────────────────────────────────

    public function openAddItem(): void
    {
        $this->resetItemForm();
        $this->showItemModal = true;
    }

    public function openEditItem(int $id): void
    {
        $item = WorkOrderItem::findOrFail($id);
        $this->editing_item_id  = $item->id;
        $this->item_type        = $item->item_type;
        $this->item_description = $item->description;
        $this->item_quantity    = $item->quantity;
        $this->item_unit_price  = $item->unit_price;
        $this->item_discount    = $item->discount_percentage;
        $this->item_status      = $item->status;
        $this->item_notes       = $item->technician_notes ?? '';
        $this->catalog_item_id  = $item->catalog_item_id;
        $this->showItemModal    = true;
    }

    public function fillFromCatalog(int $catalogId, string $type): void
    {
        $this->catalog_item_id = $catalogId;
        if ($type === 'servicio') {
            $s = ServiceCatalog::find($catalogId);
            if ($s) { $this->item_description = $s->name; $this->item_unit_price = $s->default_price; $this->item_type = 'servicio'; }
        } else {
            $p = SparePartCatalog::find($catalogId);
            if ($p) { $this->item_description = $p->name; $this->item_unit_price = $p->unit_price; $this->item_type = 'repuesto'; }
        }
    }

    public function saveItem(): void
    {
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
            'status'              => $this->item_status,
            'technician_notes'    => $this->item_notes ?: null,
            'catalog_item_id'     => $this->catalog_item_id,
            'catalog_item_type'   => $this->catalog_item_id
                ? ($this->item_type === 'servicio' ? 'services_catalog' : 'spare_parts_catalog')
                : null,
        ];

        if ($this->editing_item_id) {
            WorkOrderItem::findOrFail($this->editing_item_id)->update($data);
        } else {
            $this->workOrder->items()->create($data);
        }

        $this->workOrder->recalculateTotals();
        $this->workOrder->refresh();
        $this->closeItemModal();
    }

    public function deleteItem(int $id): void
    {
        WorkOrderItem::findOrFail($id)->delete();
        $this->workOrder->recalculateTotals();
        $this->workOrder->refresh();
    }

    public function updateItemStatus(int $id, string $status): void
    {
        WorkOrderItem::findOrFail($id)->update(['status' => $status]);
        $this->workOrder->refresh();
    }

    public function closeItemModal(): void
    {
        $this->showItemModal = false;
        $this->resetItemForm();
    }

    private function resetItemForm(): void
    {
        $this->editing_item_id = $this->catalog_item_id = null;
        $this->item_type = 'servicio';
        $this->item_description = $this->item_notes = '';
        $this->item_quantity = '1';
        $this->item_unit_price = $this->item_discount = '0';
        $this->item_status = 'pendiente';
        $this->resetValidation();
    }

    // ── Finalizar OT ─────────────────────────────────────────────────────────

    public function openFinalizeModal(): void
    {
        $this->km_exit = $this->workOrder->km_entry ?? '';
        $this->showFinalizeModal = true;
    }

    public function finalizeWorkOrder(): void
    {
        $workOrder = FinalizeWorkOrderAction::run(
            $this->workOrder,
            $this->km_exit ? (int) $this->km_exit : null,
            $this->work_description ?: null
        );
        $this->workOrder = $workOrder;
        $this->showFinalizeModal = false;
        $this->dispatch('swal', ['title' => 'OT finalizada correctamente', 'icon' => 'success']);
    }

    // ── Remisión ─────────────────────────────────────────────────────────────

    public function openRemissionModal(): void
    {
        $this->remission_notes  = '';
        $this->showRemissionModal = true;
    }

    public function createRemission(): void
    {
        Remission::create([
            'business_id'  => $this->workOrder->business_id,
            'work_order_id'=> $this->workOrder->id,
            'reference'    => Remission::generateReference($this->workOrder->business_id),
            'status'       => 'emitida',
            'notes'        => $this->remission_notes ?: null,
            'issued_at'    => now(),
            'created_by'   => auth()->id(),
        ]);
        $this->workOrder->refresh();
        $this->showRemissionModal = false;
        $this->dispatch('swal', ['title' => 'Remisión creada', 'icon' => 'success']);
    }

    public function updateRemissionStatus(int $id, string $status): void
    {
        $remission = Remission::findOrFail($id);
        $updates = ['status' => $status];
        if ($status === 'entregada') $updates['delivered_at'] = now();
        $remission->update($updates);
        $this->workOrder->refresh();
    }

    // ── Factura ──────────────────────────────────────────────────────────────

    public function openInvoiceModal(): void
    {
        $this->invoice_due_date = now()->addDays(15)->format('Y-m-d');
        $this->invoice_notes    = '';
        $this->showInvoiceModal = true;
    }

    public function generateInvoice(): void
    {
        GenerateWorkOrderInvoiceAction::run($this->workOrder, [
            'due_date' => $this->invoice_due_date ?: null,
            'notes'    => $this->invoice_notes ?: null,
        ]);
        $this->workOrder->refresh();
        $this->showInvoiceModal = false;
        $this->dispatch('swal', ['title' => 'Factura generada', 'icon' => 'success']);
    }

    public function openPaymentModal(int $invoiceId): void
    {
        $this->paying_invoice_id = $invoiceId;
        $this->payment_method    = '';
        $this->payment_reference = '';
        $this->paid_at           = now()->format('Y-m-d');
        $this->payment_notes     = '';
        $this->showPaymentModal  = true;
    }

    public function registerPayment(): void
    {
        $invoice = WorkOrderInvoice::findOrFail($this->paying_invoice_id);
        RegisterInvoicePaymentAction::run(
            $invoice,
            $this->payment_method,
            $this->payment_reference ?: null,
            $this->paid_at,
            $this->payment_notes ?: null
        );
        $this->workOrder->refresh();
        $this->showPaymentModal = false;
        $this->dispatch('swal', ['title' => 'Pago registrado correctamente', 'icon' => 'success']);
    }

    // ── Orden de Compra ───────────────────────────────────────────────────────

    public function openPOModal(): void
    {
        $this->po_supplier_name     = '';
        $this->po_supplier_nit      = '';
        $this->po_supplier_phone    = '';
        $this->po_expected_delivery = now()->addDays(5)->format('Y-m-d');
        $this->po_notes             = '';
        $this->po_items             = [['description' => '', 'quantity' => '1', 'unit_price' => '0']];
        $this->showPOModal          = true;
    }

    public function addPOItem(): void
    {
        $this->po_items[] = ['description' => '', 'quantity' => '1', 'unit_price' => '0'];
    }

    public function removePOItem(int $index): void
    {
        array_splice($this->po_items, $index, 1);
    }

    public function savePurchaseOrder(): void
    {
        $this->validate([
            'po_supplier_name'       => 'required|string|max:150',
            'po_items.*.description' => 'required|string|max:200',
            'po_items.*.quantity'    => 'required|numeric|min:0.01',
            'po_items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        $po = PurchaseOrder::create([
            'business_id'       => $this->workOrder->business_id,
            'work_order_id'     => $this->workOrder->id,
            'reference'         => PurchaseOrder::generateReference($this->workOrder->business_id),
            'supplier_name'     => $this->po_supplier_name,
            'supplier_nit'      => $this->po_supplier_nit ?: null,
            'supplier_phone'    => $this->po_supplier_phone ?: null,
            'status'            => 'borrador',
            'expected_delivery' => $this->po_expected_delivery ?: null,
            'notes'             => $this->po_notes ?: null,
            'created_by'        => auth()->id(),
        ]);

        foreach ($this->po_items as $item) {
            $qty      = (float) ($item['quantity'] ?? 1);
            $price    = (float) ($item['unit_price'] ?? 0);
            $subtotal = round($qty * $price, 2);
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'description'       => $item['description'],
                'quantity'          => $qty,
                'unit_price'        => $price,
                'subtotal'          => $subtotal,
            ]);
        }

        $po->recalculateTotal();
        $this->workOrder->refresh();
        $this->showPOModal = false;
        $this->dispatch('swal', ['title' => 'Orden de compra creada', 'icon' => 'success']);
    }

    public function updatePOStatus(int $id, string $status): void
    {
        PurchaseOrder::findOrFail($id)->update(['status' => $status]);
        $this->workOrder->refresh();
    }

    public function startProcessing(): void
    {
        if ($this->workOrder->status === 'abierta') {
            $this->workOrder->update(['status' => 'en_proceso']);
            $this->workOrder->refresh();
        }
    }

    public function render()
    {
        $business_id = auth()->user()->business_id;
        $services    = ServiceCatalog::where('business_id', $business_id)->active()->orderBy('name')->get();
        $spare_parts = SparePartCatalog::where('business_id', $business_id)->active()->orderBy('name')->get();

        $this->workOrder->load(['items', 'remissions', 'invoices', 'purchaseOrders.items', 'client', 'vehicle', 'quotation']);

        return view('livewire.admin.workshop.work-orders.show', compact('services', 'spare_parts'));
    }
}
