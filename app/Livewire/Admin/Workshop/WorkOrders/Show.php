<?php

namespace App\Livewire\Admin\Workshop\WorkOrders;

use App\Actions\LogEquipmentHistoricalAction;
use App\Actions\LogUserHistoricalAction;
use App\Actions\Workshop\AdjustWorkOrderItemQuantityAction;
use App\Actions\Workshop\SaveWorkOrderDocumentClientAction;
use App\Actions\Workshop\UpdateWorkOrderStatusAction;
use App\Enums\WorkOrderStatus;
use App\Models\GeneralConfig;
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

    public ?string $selected_document_label = null;

    public string $document_input_value = '';

    public ?int $editing_item_id = null;

    public ?int $product_type_id = null;

    public ?int $product_id = null;

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

        $this->workOrder = $workOrder;
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
            'technician_notes'    => $this->item_notes ?: null,
        ];

        $is_editing_item = (bool) $this->editing_item_id;

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
        } else {
            $this->workOrder->items()->create($data);
        }

        $this->workOrder->recalculateTotals();
        $this->workOrder->refresh();

        $description = ($is_editing_item ? 'Actualizó un ítem' : 'Agregó un ítem') . " en la OT {$this->workOrder->reference}";
        $properties = [
            'item_description' => $data['description'],
            'item_action'      => $is_editing_item ? 'updated' : 'created',
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

        LogEquipmentHistoricalAction::run(
            action: 'updated',
            module: 'workshop.work-orders',
            description: $description,
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
            ->whereKey($id)
            ->firstOrFail();

        $description = $item->description;
        $item->delete();

        $this->workOrder->recalculateTotals();
        $this->workOrder->refresh();

        $log_description = "Eliminó un ítem en la OT {$this->workOrder->reference}";
        $properties = [
            'item_description' => $description,
            'item_action'      => 'deleted',
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
        $this->assertWorkOrderEditable();

        $item = AdjustWorkOrderItemQuantityAction::run($this->workOrder->id, $id, $action);

        $this->workOrder->refresh();

        $log_description = $action === AdjustWorkOrderItemQuantityAction::ACTION_COMPLETE
            ? "Completó cantidad de un ítem en la OT {$this->workOrder->reference}"
            : "Canceló cantidad de un ítem en la OT {$this->workOrder->reference}";

        $properties = [
            'item_description'  => $item->description,
            'item_action'       => $action,
            'quantity_complete' => (float) $item->quantity_complete,
            'quantity_canceled' => (float) $item->quantity_canceled,
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
            subject: $this->workOrder,
            properties: $properties,
            business_id: (int) $this->workOrder->business_id,
        );
    }

    public function openDocumentModal(): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);
        $this->assertWorkOrderEditable();

        $this->workOrder->refresh();

        $documents = $this->workOrder->document_client ?? [];

        if ($documents !== []) {
            $label = (string) array_key_first($documents);
            $this->selected_document_label = $label;
            $this->document_input_value = is_string($documents[$label] ?? null)
                ? (string) $documents[$label]
                : '';
        } else {
            $this->selected_document_label = null;
            $this->document_input_value = '';
        }

        $this->showDocumentModal = true;
        $this->resetValidation();
    }

    public function closeDocumentModal(): void
    {
        $this->showDocumentModal = false;
        $this->selected_document_label = null;
        $this->document_input_value = '';
        $this->resetValidation();
    }

    public function loadDocumentClient(string $label): void
    {
        $this->selected_document_label = $label;
        $existing = $this->workOrder->document_client[$label] ?? '';
        $this->document_input_value = is_string($existing) ? $existing : '';
        $this->resetValidation();
    }

    public function saveDocumentClient(): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);
        $this->assertWorkOrderEditable();

        $this->validate([
            'selected_document_label' => 'required|string|max:100',
            'document_input_value'    => 'required|string|max:255',
        ], [
            'selected_document_label.required' => 'Selecciona un documento asociado.',
            'document_input_value.required'    => 'El valor del documento es obligatorio.',
        ]);

        try {
            $this->workOrder = SaveWorkOrderDocumentClientAction::run(
                $this->workOrder->id,
                $this->selected_document_label,
                $this->document_input_value,
            );
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
            'title' => 'Documento actualizado',
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

        $associated_documents = GeneralConfig::query()
            ->forAuthUser()
            ->associatedDocumentsOt()
            ->where('business_id', $this->workOrder->business_id)
            ->orderBy('value')
            ->get(['id', 'label', 'value']);

        $can_edit = auth()->user()->can('workshop.work-orders.edit');
        $is_locked = ! $this->workOrder->isEditable();

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
            'associated_documents' => $associated_documents,
            'can_edit' => $can_edit,
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
        ]);
    }
}
