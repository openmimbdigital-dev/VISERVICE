<?php

namespace App\Livewire\Admin\Workshop\WorkOrders;

use App\Actions\LogEquipmentHistoricalAction;
use App\Actions\LogUserHistoricalAction;
use App\Actions\Workshop\AdjustWorkOrderItemQuantityAction;
use App\Actions\Workshop\CreateOrUpdateWorkOrderAssociatedDocumentAction;
use App\Actions\Workshop\CreateWorkOrderInvoiceFromWorkOrderAction;
use App\Actions\Workshop\UpdateWorkOrderStatusAction;
use App\Enums\WorkOrderStatus;
use App\Models\AssociatedDocumentType;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Status;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Orden de Trabajo')]
class Show extends Component
{
    public WorkOrder $workOrder;

    public string $status = '';

    public string $status_comment = '';

    public bool $showItemModal = false;

    public bool $showDocumentModal = false;

    public ?int $selected_document_type_id = null;

    public ?string $editing_document_name = null;

    public string $document_input_value = '';

    public bool $send_invoice_value = false;

    public ?int $editing_associated_document_id = null;

    public ?int $editing_item_id = null;

    public ?int $product_type_id = null;

    public ?int $product_id = null;

    public ?int $item_equipment_id = null;

    public string $item_description = '';

    public string $item_quantity = '1';

    public string $item_unit_price = '0';

    public string $item_discount = '0';

    public string $item_notes = '';

    public function mount(WorkOrder $workOrder): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.view'), 403);

        abort_unless(
            WorkOrder::query()->forAuthUser()->whereKey($workOrder->id)->exists(),
            404
        );

        $this->workOrder = $workOrder->load('equipments:id');
        $this->status = $workOrder->status instanceof WorkOrderStatus
            ? $workOrder->status->value
            : (string) $workOrder->status;
    }

    public function updateStatus(): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);

        if (! $this->workOrder->canChangeStatus()) {
            $this->dispatch('swal', [
                'title' => 'OT bloqueada',
                'text' => 'No se puede cambiar el estado de una OT finalizada o cancelada.',
                'icon' => 'warning',
            ]);

            return;
        }

        $allowed = array_keys(Status::optionsForModule('work_orders'));

        $this->validate([
            'status' => ['required', 'string', Rule::in($allowed)],
            'status_comment' => [
                'nullable',
                'string',
                'max:1000',
                Rule::requiredIf(fn () => $this->status === WorkOrderStatus::Cancelled->value),
            ],
        ], [
            'status.required' => 'Selecciona un estado.',
            'status.in' => 'El estado seleccionado no es válido.',
            'status_comment.required' => 'Indica el motivo de la cancelación.',
            'status_comment.max' => 'El comentario no puede superar 1000 caracteres.',
        ]);

        $new_status = WorkOrderStatus::from($this->status);

        if ($new_status === $this->workOrder->status) {
            $this->dispatch('swal', [
                'title' => 'Sin cambios',
                'text' => 'La OT ya tiene ese estado.',
                'icon' => 'info',
            ]);

            return;
        }

        try {
            $this->workOrder = UpdateWorkOrderStatusAction::run(
                $this->workOrder->id,
                $new_status,
                $this->status_comment !== '' ? $this->status_comment : null,
            );
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first()
                ?? 'No se pudo actualizar el estado.';

            $this->dispatch('swal', [
                'title' => $message,
                'icon' => 'error',
            ]);

            return;
        }

        $this->status = $this->workOrder->status->value;
        $this->status_comment = '';

        $this->dispatch('swal', [
            'title' => 'Estado actualizado',
            'text' => 'La OT ahora está: '.$this->workOrder->status->label(),
            'icon' => 'success',
        ]);
    }

    public function openAddItem(): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);
        $this->assertWorkOrderEditable();

        $this->resetItemForm();
        $this->workOrder->loadMissing('equipments:id');
        $equipment_ids = $this->workOrder->equipments->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $this->item_equipment_id = count($equipment_ids) === 1 ? $equipment_ids[0] : null;
        $this->showItemModal = true;
    }

    public function openEditItem(int $id): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);
        $this->assertWorkOrderEditable();

        $item = WorkOrderItem::query()
            ->where('work_order_id', $this->workOrder->id)
            ->findOrFail($id);

        $this->editing_item_id = $item->id;
        $this->item_equipment_id = $item->equipment_id;
        $this->product_type_id = $item->product_type_id;
        $this->product_id = $item->product_id;
        $this->item_description = $item->description;
        $this->item_quantity = (string) $item->quantity;
        $this->item_unit_price = (string) $item->unit_price;
        $this->item_discount = (string) $item->discount_percentage;
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
        abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);
        $this->assertWorkOrderEditable();

        $this->workOrder->loadMissing('equipments:id');
        $allowed_equipment_ids = $this->workOrder->equipments->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->validate([
            'item_equipment_id' => ['required', 'integer', Rule::in($allowed_equipment_ids)],
            'item_description' => 'required|string|max:255',
            'product_type_id'  => 'nullable|integer|exists:product_types,id',
            'product_id'       => 'nullable|integer|exists:products,id',
            'item_quantity'    => 'required|numeric|min:0.01',
            'item_unit_price'  => 'required|numeric|min:0',
            'item_discount'    => 'nullable|numeric|min:0|max:100',
        ], [
            'item_equipment_id.required' => 'Selecciona el equipo del ítem.',
            'item_equipment_id.in' => 'El equipo seleccionado no pertenece a esta OT.',
        ]);

        $qty = (float) $this->item_quantity;
        $price = (float) $this->item_unit_price;
        $discount = (float) $this->item_discount;
        $subtotal = round(($qty * $price) * (1 - $discount / 100), 2);

        $data = [
            'equipment_id'        => (int) $this->item_equipment_id,
            'product_id'          => $this->product_id ?: null,
            'product_type_id'     => $this->product_type_id ?: null,
            'description'         => $this->item_description,
            'quantity'            => $qty,
            'unit_price'          => $price,
            'discount_percentage' => $discount,
            'subtotal'            => $subtotal,
            'technician_notes'    => $this->item_notes ?: null,
        ];

        $is_editing_item = (bool) $this->editing_item_id;
        $saved_item = null;

        if ($this->editing_item_id) {
            $item = WorkOrderItem::query()
                ->where('work_order_id', $this->workOrder->id)
                ->whereKey($this->editing_item_id)
                ->firstOrFail();

            $complete = min((float) $item->quantity_complete, $qty);
            $canceled = min((float) $item->quantity_canceled, max(0, $qty - $complete));

            $data['quantity_complete'] = $complete;
            $data['quantity_canceled'] = $canceled;

            $item->update($data);
            $saved_item = $item;
        } else {
            $saved_item = $this->workOrder->items()->create($data);
        }

        $this->workOrder->recalculateTotals();
        $this->workOrder->refresh();

        $description = ($is_editing_item ? 'Actualizó un ítem' : 'Agregó un ítem') . " en la OT {$this->workOrder->reference}";
        $properties = [
            'item_description' => $data['description'],
            'item_action'      => $is_editing_item ? 'updated' : 'created',
            'equipment_id'     => $data['equipment_id'],
        ];

        LogUserHistoricalAction::run(
            action: 'updated',
            module: 'workshop.work-orders',
            description: $description,
            subject: $this->workOrder,
            subject_label: $this->workOrder->reference,
            properties: $properties,
            business_id: (int) $this->workOrder->business_id,
        );

        $saved_item->loadMissing('equipment');
        LogEquipmentHistoricalAction::run(
            action: 'updated',
            module: 'workshop.work-orders',
            description: $description,
            equipment: $saved_item->equipment,
            subject: $this->workOrder,
            properties: $properties,
            business_id: (int) $this->workOrder->business_id,
        );

        $this->closeItemModal();
    }

    public function deleteItem(int $id): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);
        $this->assertWorkOrderEditable();

        $item = WorkOrderItem::query()
            ->where('work_order_id', $this->workOrder->id)
            ->with('equipment')
            ->whereKey($id)
            ->firstOrFail();

        $description = $item->description;
        $equipment = $item->equipment;
        $item->delete();

        $this->workOrder->recalculateTotals();
        $this->workOrder->refresh();

        $log_description = "Eliminó un ítem en la OT {$this->workOrder->reference}";
        $properties = [
            'item_description' => $description,
            'item_action'      => 'deleted',
            'equipment_id'     => $equipment?->id,
        ];

        LogUserHistoricalAction::run(
            action: 'updated',
            module: 'workshop.work-orders',
            description: $log_description,
            subject: $this->workOrder,
            subject_label: $this->workOrder->reference,
            properties: $properties,
            business_id: (int) $this->workOrder->business_id,
        );

        LogEquipmentHistoricalAction::run(
            action: 'updated',
            module: 'workshop.work-orders',
            description: $log_description,
            equipment: $equipment,
            subject: $this->workOrder,
            properties: $properties,
            business_id: (int) $this->workOrder->business_id,
        );
    }

    public function completeItemQuantity(int $id): void
    {
        $this->adjustItemQuantity($id, AdjustWorkOrderItemQuantityAction::ACTION_COMPLETE);
    }

    public function cancelItemQuantity(int $id): void
    {
        $this->adjustItemQuantity($id, AdjustWorkOrderItemQuantityAction::ACTION_CANCEL);
    }

    private function adjustItemQuantity(int $id, string $action): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);

        $this->workOrder->refresh();

        if (! $this->workOrder->isEditable()) {
            $this->dispatch('swal', [
                'title' => 'OT bloqueada',
                'text'  => 'La OT está finalizada o cancelada y no admite cambios.',
                'icon'  => 'warning',
            ]);

            return;
        }

        $previous_status = $this->status;

        try {
            $item = AdjustWorkOrderItemQuantityAction::run($this->workOrder->id, $id, $action);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first()
                ?? 'No se pudo actualizar la cantidad del ítem.';

            $this->dispatch('swal', [
                'title' => $message,
                'icon'  => 'warning',
            ]);

            $this->workOrder->refresh();
            $this->status = $this->workOrder->status instanceof WorkOrderStatus
                ? $this->workOrder->status->value
                : (string) $this->workOrder->status;

            return;
        }

        $this->workOrder->refresh();
        $this->status = $this->workOrder->status instanceof WorkOrderStatus
            ? $this->workOrder->status->value
            : (string) $this->workOrder->status;

        $log_description = $action === AdjustWorkOrderItemQuantityAction::ACTION_COMPLETE
            ? "Completó cantidad de un ítem en la OT {$this->workOrder->reference}"
            : "Canceló cantidad de un ítem en la OT {$this->workOrder->reference}";

        $item->loadMissing('equipment');

        $properties = [
            'item_description'  => $item->description,
            'item_action'       => $action,
            'quantity_complete' => (float) $item->quantity_complete,
            'quantity_canceled' => (float) $item->quantity_canceled,
            'equipment_id'      => $item->equipment_id,
        ];

        LogUserHistoricalAction::run(
            action: 'updated',
            module: 'workshop.work-orders',
            description: $log_description,
            subject: $this->workOrder,
            subject_label: $this->workOrder->reference,
            properties: $properties,
            business_id: (int) $this->workOrder->business_id,
        );

        LogEquipmentHistoricalAction::run(
            action: 'updated',
            module: 'workshop.work-orders',
            description: $log_description,
            equipment: $item->equipment,
            subject: $this->workOrder,
            properties: $properties,
            business_id: (int) $this->workOrder->business_id,
        );

        if ($this->status !== $previous_status) {
            $message = match ($this->status) {
                WorkOrderStatus::Cancelled->value => 'Todos los ítems están cancelados. La OT pasó a Cancelada.',
                WorkOrderStatus::Completed->value => 'Todos los ítems están completados. La OT pasó a Finalizada.',
                WorkOrderStatus::InProgress->value => 'Se completó al menos un ítem. La OT pasó a En proceso.',
                default => 'El estado de la OT se actualizó automáticamente.',
            };

            $this->dispatch('swal', [
                'title' => 'Estado actualizado',
                'text' => $message,
                'icon' => 'success',
            ]);
        }
    }

    public function openDocumentModal(): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);
        $this->assertCanManageAssociatedDocuments();

        $this->editing_associated_document_id = null;
        $this->editing_document_name = null;
        $this->selected_document_type_id = null;
        $this->document_input_value = '';
        $this->send_invoice_value = false;
        $this->showDocumentModal = true;
        $this->resetValidation();
    }

    public function openEditAssociatedDocument(int $id): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);
        $this->assertCanManageAssociatedDocuments();

        $document = $this->workOrder->associatedDocuments()->findOrFail($id);

        $this->editing_associated_document_id = $document->id;
        $this->editing_document_name = $document->name;
        $this->selected_document_type_id = $document->associated_document_type_id;
        $this->document_input_value = (string) $document->value;
        $this->send_invoice_value = (bool) $document->send_invoice;
        $this->showDocumentModal = true;
        $this->resetValidation();
    }

    public function updatedSelectedDocumentTypeId(mixed $value): void
    {
        if (! $value) {
            $this->send_invoice_value = false;

            return;
        }

        $document_type = AssociatedDocumentType::query()
            ->forAuthUser()
            ->where('business_id', $this->workOrder->business_id)
            ->find($value);

        $this->send_invoice_value = (bool) $document_type?->send_invoice;
    }

    public function closeDocumentModal(): void
    {
        $this->showDocumentModal = false;
        $this->editing_associated_document_id = null;
        $this->editing_document_name = null;
        $this->selected_document_type_id = null;
        $this->document_input_value = '';
        $this->send_invoice_value = false;
        $this->resetValidation();
    }

    public function saveDocumentClient(): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);
        $this->assertCanManageAssociatedDocuments();

        $this->validate([
            'selected_document_type_id' => 'required|integer',
            'document_input_value'      => 'required|string|max:255',
        ], [
            'selected_document_type_id.required' => 'Selecciona un documento asociado.',
            'document_input_value.required'      => 'El valor del documento es obligatorio.',
        ]);

        $is_editing = (bool) $this->editing_associated_document_id;

        try {
            CreateOrUpdateWorkOrderAssociatedDocumentAction::run(
                $this->workOrder->id,
                $this->editing_associated_document_id,
                $this->selected_document_type_id,
                $this->document_input_value,
                $this->send_invoice_value,
            );
            $this->workOrder->refresh();
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first()
                ?? 'No se pudo guardar el documento.';

            $this->dispatch('swal', [
                'title' => $message,
                'icon' => 'error',
            ]);

            return;
        }

        $this->closeDocumentModal();

        $this->dispatch('swal', [
            'title' => $is_editing ? 'Documento actualizado' : 'Documento asociado',
            'icon'  => 'success',
        ]);
    }

    public function invoiceWorkOrder(): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);

        try {
            $invoice = CreateWorkOrderInvoiceFromWorkOrderAction::run($this->workOrder);
            $this->workOrder->refresh()->load(['latestInvoice', 'invoices']);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first()
                ?? 'No se pudo generar la factura.';

            $this->dispatch('swal', [
                'title' => $message,
                'icon'  => 'error',
            ]);

            return;
        }

        $this->dispatch('swal', [
            'title' => "Factura {$invoice->reference} creada",
            'icon'  => 'success',
        ]);
    }

    public function closeItemModal(): void
    {
        $this->showItemModal = false;
        $this->resetItemForm();
    }

    private function resetItemForm(): void
    {
        $this->editing_item_id = null;
        $this->item_equipment_id = null;
        $this->product_type_id = null;
        $this->product_id = null;
        $this->item_description = '';
        $this->item_notes = '';
        $this->item_quantity = '1';
        $this->item_unit_price = '0';
        $this->item_discount = '0';
        $this->resetValidation();
    }

    private function assertWorkOrderEditable(): void
    {
        $this->workOrder->refresh();

        abort_unless(
            $this->workOrder->isEditable(),
            403,
            'La OT está finalizada o cancelada y no admite cambios.'
        );
    }

    private function assertCanManageAssociatedDocuments(): void
    {
        $this->workOrder->refresh();

        abort_unless(
            $this->workOrder->canManageAssociatedDocuments(),
            403,
            'La OT está cancelada y no admite documentos asociados.'
        );
    }

    public function render()
    {
        $this->workOrder->load([
            'items.productType',
            'items.catalogProduct',
            'items.equipment',
            'client',
            'equipments',
            'quotation',
            'remissions',
            'associatedDocuments',
            'latestInvoice',
            'invoices',
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

        $associated_document_types = AssociatedDocumentType::query()
            ->forAuthUser()
            ->where('business_id', $this->workOrder->business_id)
            ->where(function ($q) {
                $q->where('active', true);
                if ($this->selected_document_type_id) {
                    $q->orWhere('id', $this->selected_document_type_id);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        $used_document_type_ids = $this->workOrder->associatedDocuments->pluck('associated_document_type_id')->filter()->all();
        $available_associated_documents = $associated_document_types
            ->reject(function ($type) use ($used_document_type_ids) {
                if ($this->editing_associated_document_id
                    && (int) $this->selected_document_type_id === (int) $type->id
                ) {
                    return false;
                }

                return in_array($type->id, $used_document_type_ids, true);
            })
            ->values();

        $can_edit = auth()->user()->can('workshop.work-orders.edit');
        $is_locked = ! $this->workOrder->isEditable();
        $linked_remission = $this->workOrder->remissions->first();
        $can_create_remission = auth()->user()->can('workshop.remissions.create')
            && $this->workOrder->canReceiveRemission()
            && ! $linked_remission;

        $has_active_invoice = $this->workOrder->invoices
            ->whereIn('status', ['pendiente', 'pagada'])
            ->isNotEmpty();

        $can_invoice = $can_edit
            && $this->workOrder->status === WorkOrderStatus::Completed
            && ! $has_active_invoice;

        $status_badge_class = $this->workOrder->status instanceof WorkOrderStatus
            ? $this->workOrder->status->badgeClass()
            : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

        $status_comments_history = collect($this->workOrder->status_comments ?? [])
            ->reverse()
            ->values()
            ->map(function (array $entry) {
                $status_enum = WorkOrderStatus::tryFrom($entry['status'] ?? '');

                return [
                    'comment' => $entry['comment'] ?? '',
                    'status_label' => $status_enum?->label() ?? ($entry['status'] ?? ''),
                    'user_name' => $entry['user_name'] ?? null,
                    'changed_at' => ! empty($entry['changed_at'])
                        ? \Illuminate\Support\Carbon::parse($entry['changed_at'])->format('d/m/Y H:i')
                        : null,
                ];
            })
            ->all();

        return view('livewire.admin.workshop.work-orders.show', [
            'product_types' => $product_types,
            'catalog_products' => $catalog_products,
            'available_associated_documents' => $available_associated_documents,
            'can_edit' => $can_edit,
            'can_manage_documents' => $can_edit && $this->workOrder->canManageAssociatedDocuments(),
            'documents_disabled_title' => 'La OT está cancelada y no admite documentos asociados',
            'can_edit_items' => $can_edit && ! $is_locked,
            'can_manage' => $can_edit && ! $is_locked,
            'edit_disabled' => $is_locked,
            'edit_disabled_title' => 'La OT está finalizada o cancelada',
            'can_change_status' => $can_edit,
            'status_change_disabled' => $is_locked,
            'status_options' => Status::optionsForModule('work_orders'),
            'status_badge_class' => $status_badge_class,
            'show_cancel_comment_required' => $this->status === WorkOrderStatus::Cancelled->value,
            'status_comment_placeholder' => $this->status === WorkOrderStatus::Cancelled->value
                ? 'Motivo de la cancelación…'
                : 'Opcional: nota del cambio de estado…',
            'status_comments_history' => $status_comments_history,
            'can_create_remission' => $can_create_remission,
            'linked_remission' => $linked_remission,
            'can_invoice' => $can_invoice,
            'latest_invoice' => $this->workOrder->latestInvoice,
        ]);
    }
}
