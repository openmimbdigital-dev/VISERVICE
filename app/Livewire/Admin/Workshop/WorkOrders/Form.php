<?php

namespace App\Livewire\Admin\Workshop\WorkOrders;

use App\Actions\Workshop\CreateOrUpdateWorkOrderAction;
use App\Actions\Workshop\DeleteWorkOrderAction;
use App\Enums\QuotationStatus;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Forms\Admin\Workshop\WorkOrderForm;
use App\Models\Client;
use App\Models\Coupon;
use App\Models\Equipment;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Quotation;
use App\Models\WorkOrder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Orden de Trabajo')]
class Form extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public WorkOrderForm $form;

    public int $step = WorkOrderForm::STEP_GENERAL;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public ?string $reference = null;

    /** Bloquea el select de cotización cuando se abre el form desde una cotización. */
    public bool $quotation_locked = false;

    public string $catalog_search = '';

    public ?int $catalog_type_filter = null;

    public int $catalog_visible_count = 20;

    public ?int $preview_product_id = null;

    /** @var array<int, string> */
    public array $catalog_quantities = [];

    public ?int $active_equipment_id = null;

    /** Código que el usuario escribe para aplicar un cupón a toda la OT. */
    public string $coupon_input = '';

    public function mount(?WorkOrder $workOrder = null): void
    {
        if ($workOrder) {
            abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);

            abort_unless(
                WorkOrder::query()->forAuthUser()->whereKey($workOrder->id)->exists(),
                404
            );

            abort_unless($workOrder->status?->isOpen() ?? false, 403);

            $workOrder->load(['items.productType', 'items.catalogProduct', 'equipments:id']);
            $this->form->setWorkOrder($workOrder);
            $this->step = $workOrder->isComplete()
                ? WorkOrderForm::STEP_GENERAL
                : max(WorkOrderForm::STEP_GENERAL, min((int) $workOrder->step, WorkOrderForm::TOTAL_STEPS));
            $this->reference = $workOrder->reference;
            $this->quotation_locked = (bool) $workOrder->quotation_id;
            $this->items = $workOrder->items->map(fn ($item) => [
                'uid'                 => 'woi-'.$item->id,
                'id'                  => $item->id,
                'equipment_id'        => $item->equipment_id,
                'product_type_id'     => $item->product_type_id,
                'product_id'          => $item->product_id,
                'description'         => $item->description,
                'quantity'            => (string) $item->quantity,
                'unit_price'          => (string) $item->unit_price,
                'discount_percentage' => (string) $item->discount_percentage,
            ])->values()->all();

            $this->syncActiveEquipment();

            return;
        }

        abort_unless(auth()->user()?->can('workshop.work-orders.create'), 403);

        $quotation_id = request()->integer('quotation');
        if ($quotation_id > 0) {
            $this->applyQuotationFromId($quotation_id);
            if ($this->form->quotation_id) {
                $this->quotation_locked = true;
            }
        }

        $this->syncActiveEquipment();
    }

    public function updatedFormClientId(): void
    {
        if ($this->form->quotation_id) {
            return;
        }

        $this->form->equipment_ids = [];
        $this->clearItemEquipmentAssignments();
        $this->syncActiveEquipment();
    }

    public function updatedFormEquipmentIds(): void
    {
        if ($this->form->quotation_id) {
            return;
        }

        $allowed = $this->form->resolvedEquipmentIds();
        $allowed_flip = array_flip($allowed);

        foreach ($this->items as $index => $row) {
            $equipment_id = (int) ($row['equipment_id'] ?? 0);
            if ($equipment_id > 0 && ! isset($allowed_flip[$equipment_id])) {
                $this->items[$index]['equipment_id'] = null;
            }
        }

        $this->syncActiveEquipment();
    }

    private function syncActiveEquipment(): void
    {
        $allowed = $this->form->resolvedEquipmentIds();

        if ($allowed === []) {
            $this->active_equipment_id = null;

            return;
        }

        if (count($allowed) === 1) {
            $this->active_equipment_id = $allowed[0];

            return;
        }

        if (! in_array($this->active_equipment_id, $allowed, true)) {
            $this->active_equipment_id = null;
        }
    }

    public function updatedFormQuotationId(mixed $value): void
    {
        if (! $value) {
            return;
        }

        $this->applyQuotationFromId((int) $value);
    }

    private function applyQuotationFromId(int $quotation_id): void
    {
        $quotation = Quotation::query()
            ->forAuthUser()
            ->where('business_id', $this->form->resolvedBusinessId())
            ->where('status', QuotationStatus::Accepted)
            ->where(function ($query) {
                $query->whereDoesntHave('workOrder');

                if ($this->form->work_order_id) {
                    $query->orWhereHas('workOrder', fn ($q) => $q->whereKey($this->form->work_order_id));
                }
            })
            ->with(['items', 'equipments:id'])
            ->find($quotation_id);

        if (! $quotation) {
            $this->form->quotation_id = null;
            $this->addError('form.quotation_id', 'La cotización no está disponible o ya tiene una OT.');

            return;
        }

        $this->form->applyQuotation($quotation);

        $this->items = $quotation->items->map(fn ($item) => [
            'uid'                 => 'qi-'.$item->id.'-'.uniqid(),
            'id'                  => null,
            'equipment_id'        => $item->equipment_id,
            'product_type_id'     => $item->product_type_id,
            'product_id'          => $item->product_id,
            'description'         => $item->description,
            'quantity'            => (string) $item->quantity,
            'unit_price'          => (string) $item->unit_price,
            'discount_percentage' => (string) $item->discount_percentage,
        ])->values()->all();

        $this->syncActiveEquipment();
    }

    public function updatedCatalogSearch(): void
    {
        $this->catalog_visible_count = 20;
    }

    public function updatedCatalogTypeFilter(): void
    {
        $this->catalog_visible_count = 20;
    }

    public function loadMoreCatalogProducts(): void
    {
        $this->catalog_visible_count += 20;
    }

    public function showProductPreview(int $product_id): void
    {
        $exists = Product::query()
            ->forAuthUser()
            ->where('business_id', $this->form->resolvedBusinessId())
            ->whereKey($product_id)
            ->exists();

        if (! $exists) {
            return;
        }

        $this->preview_product_id = $product_id;
    }

    public function closeProductPreview(): void
    {
        $this->preview_product_id = null;
    }

    public function addItem(): void
    {
        $equipment_ids = $this->form->resolvedEquipmentIds();

        $this->items[] = [
            'uid'                 => uniqid('wo-item-', true),
            'id'                  => null,
            'equipment_id'        => $this->active_equipment_id ?: (count($equipment_ids) === 1 ? $equipment_ids[0] : null),
            'product_type_id'     => null,
            'product_id'          => null,
            'description'         => '',
            'quantity'            => '1',
            'unit_price'          => '0',
            'discount_percentage' => '0',
        ];
    }

    public function addCatalogItem(int $product_id): void
    {
        $equipment_ids = $this->form->resolvedEquipmentIds();
        $equipment_id  = $this->active_equipment_id ?: (count($equipment_ids) === 1 ? $equipment_ids[0] : null);

        if (! $equipment_id || ! in_array($equipment_id, $equipment_ids, true)) {
            $this->addError('active_equipment_id', 'Selecciona el equipo al que se aplicará el producto.');

            return;
        }

        $catalog = Product::query()
            ->forAuthUser()
            ->complete()
            ->where('business_id', $this->form->resolvedBusinessId())
            ->active()
            ->whereKey($product_id)
            ->first();

        if (! $catalog) {
            return;
        }

        $quantity = (float) ($this->catalog_quantities[$product_id] ?? 1);
        if ($quantity <= 0) {
            $quantity = 1;
        }

        foreach ($this->items as $index => $row) {
            $same_product   = (int) ($row['product_id'] ?? 0) === (int) $catalog->id;
            $same_equipment = (int) ($row['equipment_id'] ?? 0) === $equipment_id;

            if ($same_product && $same_equipment) {
                $this->items[$index]['quantity'] = (string) ((float) $this->items[$index]['quantity'] + $quantity);
                $this->catalog_quantities[$product_id] = '1';

                return;
            }
        }

        $this->items[] = [
            'uid'                 => uniqid('wo-item-', true),
            'id'                  => null,
            'equipment_id'        => $equipment_id,
            'product_type_id'     => $catalog->product_type_id,
            'product_id'          => $catalog->id,
            'description'         => $catalog->name,
            'quantity'            => (string) $quantity,
            'unit_price'          => (string) $catalog->sale_price,
            // El descuento del catálogo llega como sugerencia y se puede ajustar en la línea.
            'discount_percentage' => (string) $catalog->discountPercentage(),
        ];

        $this->catalog_quantities[$product_id] = '1';
    }

    /**
     * La cantidad se mueve con los botones + / −: el precio y el descuento de un
     * producto vienen del catálogo, así que la cantidad es lo único ajustable.
     */
    public function changeItemQuantity(int $index, float $delta): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $quantity = (float) ($this->items[$index]['quantity'] ?? 1) + $delta;

        // No baja de uno: para dejar la línea en cero está el botón de quitar.
        $this->items[$index]['quantity'] = $this->formatQuantity(max(1, $quantity));
    }

    private function formatQuantity(float $quantity): string
    {
        return rtrim(rtrim(number_format($quantity, 2, '.', ''), '0'), '.') ?: '1';
    }

    /** Busca el cupón por código y lo aplica a toda la OT. */
    public function applyCoupon(): void
    {
        $this->resetValidation('coupon_input');

        $code = Coupon::normalizeCode($this->coupon_input);

        if ($code === '') {
            $this->addError('coupon_input', 'Escribe el código del cupón.');

            return;
        }

        $coupon = Coupon::query()
            ->where('business_id', $this->form->resolvedBusinessId())
            ->where('code', $code)
            ->first();

        if (! $coupon) {
            $this->addError('coupon_input', 'No existe un cupón con ese código.');

            return;
        }

        $reason = $coupon->unavailableReason($this->previewSubtotal(), $this->form->work_order_id);

        if ($reason !== null) {
            $this->addError('coupon_input', $reason);

            return;
        }

        $this->form->coupon_id   = $coupon->id;
        $this->form->coupon_code = $coupon->code;
        $this->coupon_input      = '';

        $this->dispatch('swal', [
            'title' => "Cupón {$coupon->code} aplicado",
            'text'  => 'Descuento de '.$coupon->discountLabel().' sobre el subtotal.',
            'icon'  => 'success',
        ]);
    }

    public function removeCoupon(): void
    {
        $this->form->coupon_id   = null;
        $this->form->coupon_code = '';
        $this->coupon_input      = '';
        $this->resetValidation('coupon_input');
    }

    public function removeItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();
        $this->advanceToStep(min($this->step + 1, WorkOrderForm::TOTAL_STEPS));
    }

    public function previousStep(): void
    {
        $this->step = max($this->step - 1, WorkOrderForm::STEP_GENERAL);
    }

    public function goToStep(int $step): void
    {
        if ($step < WorkOrderForm::STEP_GENERAL || $step > WorkOrderForm::TOTAL_STEPS) {
            return;
        }

        if ($step > $this->step) {
            for ($current = $this->step; $current < $step; $current++) {
                if ($current === WorkOrderForm::STEP_ITEMS) {
                    $this->validateItemsStep();
                } else {
                    $this->form->validate($this->form->rulesForStep($current));
                }
            }
        }

        $this->advanceToStep($step);
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()->can($this->form->isEditing() ? 'workshop.work-orders.edit' : 'workshop.work-orders.create'),
            403
        );

        $business_id = $this->form->resolvedBusinessId();
        abort_unless($business_id, 403, 'No tienes un negocio asociado.');

        $this->items = array_values(array_filter(
            $this->items,
            fn ($row) => trim((string) ($row['description'] ?? '')) !== ''
        ));

        try {
            if ($this->items !== []) {
                $this->validate($this->itemRules());
            } else {
                $this->validate(['items' => ['required', 'array', 'min:1']]);
            }

            $work_order = CreateOrUpdateWorkOrderAction::run(
                $business_id,
                $this->form->work_order_id,
                (int) $this->form->client_id,
                $this->form->resolvedEquipmentIds(),
                $this->form->validated(),
                $this->items
            );
        } catch (ValidationException $exception) {
            $this->step = $this->form->firstStepWithErrors($exception->errors());

            throw $exception;
        }

        $this->dispatch('swal', [
            'title' => $this->form->isEditing()
                ? 'Orden de trabajo guardada'
                : "OT {$work_order->reference} creada",
            'icon'  => 'success',
        ]);

        $this->redirectRoute('admin.workshop.work-orders.index', navigate: true);
    }

    public function deleteWorkOrder(): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.delete'), 403);
        abort_unless($this->form->work_order_id, 403);

        $this->askDeleteConfirmation($this->form->work_order_id, '¿Eliminar esta orden de trabajo?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteWorkOrderAction::run($this->delete_id);
            $this->alertDeleteSuccess('Orden de trabajo eliminada correctamente.');
            $this->redirectRoute('admin.workshop.work-orders.index', navigate: true);
        } catch (\Throwable $e) {
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo eliminar la OT.');
        }
    }

    /** Subtotal de los ítems en pantalla, ya con el descuento de cada producto. */
    private function previewSubtotal(): float
    {
        $subtotal = 0.0;

        foreach ($this->items as $row) {
            $base     = round((float) ($row['quantity'] ?? 0) * (float) ($row['unit_price'] ?? 0), 2);
            $subtotal += round($base * (1 - (float) ($row['discount_percentage'] ?? 0) / 100), 2);
        }

        return round($subtotal, 2);
    }

    /** @return array<string, mixed> */
    protected function itemRules(): array
    {
        $equipment_ids = $this->form->resolvedEquipmentIds();

        return [
            'items'                       => ['array'],
            'items.*.equipment_id'        => ['required', 'integer', Rule::in($equipment_ids)],
            'items.*.description'         => ['required', 'string', 'max:200'],
            'items.*.quantity'            => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'          => ['required', 'numeric', 'min:0'],
            'items.*.discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.product_type_id'     => ['nullable', 'integer', 'exists:product_types,id'],
            'items.*.product_id'          => ['nullable', 'integer', 'exists:products,id'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'items.*.equipment_id' => 'equipo del ítem',
            'items.*.description'  => 'descripción del ítem',
            'items.*.quantity'     => 'cantidad',
            'items.*.unit_price'   => 'precio unitario',
            'items'                => 'ítems',
        ];
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return [
            'items.required' => 'Agrega al menos un ítem a la orden de trabajo.',
            'items.min'      => 'Agrega al menos un ítem a la orden de trabajo.',
        ];
    }

    protected function validateCurrentStep(): void
    {
        if ($this->step === WorkOrderForm::STEP_ITEMS) {
            $this->validateItemsStep();

            return;
        }

        $this->form->validate($this->form->rulesForStep($this->step));
    }

    protected function validateItemsStep(): void
    {
        $this->items = array_values(array_filter(
            $this->items,
            fn ($row) => trim((string) ($row['description'] ?? '')) !== ''
        ));

        if ($this->items === []) {
            $this->validate(['items' => ['required', 'array', 'min:1']]);

            return;
        }

        $this->validate($this->itemRules());
    }

    protected function isFlowComplete(): bool
    {
        return $this->form->work_order_id
            && $this->step >= WorkOrderForm::TOTAL_STEPS
            && collect($this->items)->contains(fn ($row) => trim((string) ($row['description'] ?? '')) !== '');
    }

    protected function advanceToStep(int $step): void
    {
        if ($step > $this->step && ! $this->isFlowComplete()) {
            $was_new    = ! $this->form->isEditing();
            $work_order = $this->persistProgress($step);

            if ($was_new) {
                $this->redirectRoute('admin.workshop.work-orders.form.edit', $work_order, navigate: true);

                return;
            }
        }

        $this->step = $step;
    }

    protected function persistProgress(int $step): WorkOrder
    {
        $business_id = $this->form->resolvedBusinessId();
        abort_unless($business_id, 403);

        $work_order = CreateOrUpdateWorkOrderAction::run(
            $business_id,
            $this->form->work_order_id,
            (int) $this->form->client_id,
            $this->form->resolvedEquipmentIds(),
            $this->form->payload($step),
            $this->items
        );

        $this->form->work_order_id = $work_order->id;
        $this->reference           = $work_order->reference;

        return $work_order;
    }

    protected function clearItemEquipmentAssignments(): void
    {
        foreach ($this->items as $index => $row) {
            $this->items[$index]['equipment_id'] = null;
        }
    }

    public function render()
    {
        $business_id = $this->form->resolvedBusinessId();
        $from_quotation = (bool) $this->form->quotation_id;

        $clients = Client::query()->forAuthUser()->where('status', true)->orderBy('name')->get();
        $accepted_quotations = $this->form->getAcceptedQuotations();
        $product_types = ProductType::query()->visibleToUser()->where('active', true)->orderBy('name')->get();

        $catalog_query = Product::query()
            ->forAuthUser()
            ->complete()
            ->where('business_id', $business_id)
            ->active()
            ->with(['images', 'product_type'])
            ->when($this->catalog_type_filter, fn ($query) => $query->where('product_type_id', $this->catalog_type_filter))
            ->when(trim($this->catalog_search) !== '', function ($query) {
                $term = trim($this->catalog_search);
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%");
                });
            })
            ->orderBy('name');

        $catalog_products_total = (clone $catalog_query)->count();
        $catalog_products = $catalog_query->take($this->catalog_visible_count)->get();
        $catalog_has_more = $catalog_products_total > $catalog_products->count();

        foreach ($catalog_products as $catalog_product) {
            $this->catalog_quantities[$catalog_product->id] ??= '1';
        }

        $preview_product = null;
        if ($this->preview_product_id) {
            $this->catalog_quantities[$this->preview_product_id] ??= '1';

            $preview_product = Product::query()
                ->forAuthUser()
                ->where('business_id', $business_id)
                ->with(['images', 'product_type', 'product_category', 'unit', 'brand'])
                ->find($this->preview_product_id);
        }

        $cart_quantities = collect($this->items)
            ->filter(fn ($row) => ! empty($row['product_id']))
            ->groupBy('product_id')
            ->map(fn ($rows) => $rows->sum(fn ($row) => (float) ($row['quantity'] ?? 0)));

        $cart_product_ids = collect($this->items)->pluck('product_id')->filter()->unique()->values()->all();
        $cart_products = $cart_product_ids !== []
            ? Product::query()->forAuthUser()->whereIn('id', $cart_product_ids)->with('images')->get()->keyBy('id')
            : collect();

        $selected_equipment_ids = $this->form->resolvedEquipmentIds();

        $equipment_query = Equipment::query()->forAuthUser()
            ->where('client_id', $this->form->client_id)
            ->where(function ($query) use ($selected_equipment_ids) {
                $query->where(function ($complete) {
                    $complete->complete()->where('status', true);
                });

                if ($selected_equipment_ids !== []) {
                    $query->orWhereIn('id', $selected_equipment_ids);
                }
            })
            ->orderBy('name')
            ->orderBy('plate');

        $equipment_for_client = $this->form->client_id
            ? $equipment_query->get(['id', 'name', 'brand_name', 'plate'])
            : collect();

        $selected_equipments = $equipment_for_client
            ->whereIn('id', $selected_equipment_ids)
            ->values();

        $subtotal = $this->previewSubtotal();

        $applied_coupon = $this->form->coupon_id
            ? Coupon::query()->where('business_id', $business_id)->find($this->form->coupon_id)
            : null;

        if ($this->form->coupon_id && ! $applied_coupon) {
            // El cupón se eliminó mientras la OT estaba abierta.
            $this->form->coupon_id   = null;
            $this->form->coupon_code = '';
        }

        $coupon_discount = $applied_coupon ? $applied_coupon->discountOn($subtotal) : 0.0;
        $taxable_base    = max(0, round($subtotal - $coupon_discount, 2));

        $tax_pct = (float) ($this->form->tax_percentage ?: 0);
        $tax     = round($taxable_base * ($tax_pct / 100), 2);
        $total   = $taxable_base + $tax;
        $advance_pct = (float) ($this->form->advance_percentage ?: 0);
        $advance_amount = round($taxable_base * ($advance_pct / 100), 2);
        $this->form->advance_amount = (string) $advance_amount;

        $item_line_totals   = [];
        $item_line_discounts = [];
        $items_discount_total = 0.0;
        foreach ($this->items as $index => $row) {
            $base     = round((float) ($row['quantity'] ?? 0) * (float) ($row['unit_price'] ?? 0), 2);
            $discount = round($base * ((float) ($row['discount_percentage'] ?? 0) / 100), 2);

            $item_line_totals[$index]    = round($base - $discount, 2);
            $item_line_discounts[$index] = $discount;
            $items_discount_total += $discount;
        }

        $linked_remission = null;
        $can_create_remission = false;
        if ($this->form->work_order_id) {
            $work_order = WorkOrder::query()
                ->forAuthUser()
                ->with('remissions')
                ->find($this->form->work_order_id);

            $linked_remission = $work_order?->remissions->first();
            $can_create_remission = auth()->user()->can('workshop.remissions.create')
                && ($work_order?->canReceiveRemission() ?? false)
                && ! $linked_remission;
        }

        $total_steps   = WorkOrderForm::TOTAL_STEPS;
        $progress      = (int) round(($this->step / $total_steps) * 100);
        $radius        = 30;
        $circumference = round(2 * M_PI * $radius, 2);

        return view('livewire.admin.workshop.work-orders.form', [
            'is_editing'             => $this->form->isEditing(),
            'from_quotation'         => $from_quotation,
            'step'                   => $this->step,
            'total_steps'            => $total_steps,
            'progress'               => $progress,
            'progress_circumference' => $circumference,
            'progress_offset'        => round($circumference * (1 - $progress / 100), 2),
            'steps'                  => [
                WorkOrderForm::STEP_GENERAL => [
                    'title'       => 'Datos',
                    'description' => 'Cliente y equipos',
                ],
                WorkOrderForm::STEP_CONDITIONS => [
                    'title'       => 'Condiciones',
                    'description' => 'Entrega, IVA y anticipo',
                ],
                WorkOrderForm::STEP_ITEMS => [
                    'title'       => 'Ítems',
                    'description' => 'Productos y servicios',
                ],
            ],
            'clients'              => $clients,
            'accepted_quotations'  => $accepted_quotations,
            'product_types'        => $product_types,
            'catalog_products'     => $catalog_products,
            'catalog_has_more'     => $catalog_has_more,
            'preview_product'      => $preview_product,
            'cart_quantities'      => $cart_quantities,
            'cart_products'        => $cart_products,
            'equipment_for_client' => $equipment_for_client,
            'selected_equipments'  => $selected_equipments,
            'preview_subtotal'     => $subtotal,
            'preview_tax'          => $tax,
            'preview_total'        => $total,
            'preview_advance_amount' => $advance_amount,
            'applied_coupon'       => $applied_coupon,
            'coupon_discount'      => $coupon_discount,
            'items_discount_total' => round($items_discount_total, 2),
            'item_line_totals'     => $item_line_totals,
            'item_line_discounts'  => $item_line_discounts,
            'can_delete'           => $this->form->work_order_id
                && auth()->user()->can('workshop.work-orders.delete'),
            'can_create_remission' => $can_create_remission,
            'linked_remission'     => $linked_remission,
        ]);
    }
}
