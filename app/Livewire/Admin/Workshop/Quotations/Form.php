<?php

namespace App\Livewire\Admin\Workshop\Quotations;

use App\Actions\Workshop\CreateOrUpdateQuotationAction;
use App\Actions\Workshop\DeleteQuotationAction;
use App\Enums\QuotationStatus;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Concerns\ManagesPendingCustomTaxes;
use App\Livewire\Concerns\TracksWizardProgress;
use App\Livewire\Forms\Admin\Workshop\QuotationForm;
use App\Models\Equipment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\Quotation;
use App\Models\Status;
use App\Support\AppliedTaxesPreview;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cotización')]
class Form extends Component
{
    use ConfirmsDeletionWithLivewireAlert;
    use ManagesPendingCustomTaxes;
    use TracksWizardProgress;

    public QuotationForm $form;

    public int $step = QuotationForm::STEP_GENERAL;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public ?string $reference = null;

    public ?string $quotation_status = null;

    public ?int $linked_work_order_id = null;

    public ?string $linked_work_order_reference = null;

    public string $catalog_search = '';

    public ?int $catalog_type_filter = null;

    public int $catalog_visible_count = 20;

    public ?int $preview_product_id = null;

    /** @var array<int, string> */
    public array $catalog_quantities = [];

    /** @var array<int, bool> */
    public array $catalog_apply_discount = [];

    public ?int $active_equipment_id = null;

    public function mount(?Quotation $quotation = null): void
    {
        if ($quotation) {
            abort_unless(auth()->user()?->can('workshop.quotations.edit'), 403);

            abort_unless(
                Quotation::query()->forAuthUser()->whereKey($quotation->id)->exists(),
                404
            );

            $quotation->load(['items.catalogProduct', 'items.productType', 'items.productCategory', 'equipments:id', 'workOrder']);

            if (! $quotation->isEditable()) {
                $this->redirectRoute('admin.workshop.quotations.show', $quotation, navigate: true);

                return;
            }

            $this->form->setQuotation($quotation);
            $this->syncWizardProgress($quotation);
            $this->step = $quotation->isComplete()
                ? QuotationForm::STEP_GENERAL
                : max(QuotationForm::STEP_GENERAL, min((int) $quotation->step, QuotationForm::TOTAL_STEPS));
            $this->reference = $quotation->reference;
            $this->quotation_status = $quotation->status instanceof QuotationStatus
                ? $quotation->status->value
                : (string) $quotation->status;
            $this->linked_work_order_id = $quotation->workOrder?->id;
            $this->linked_work_order_reference = $quotation->workOrder?->reference;
            $this->items = $quotation->items->map(function ($item) {
                $catalog = $item->catalogProduct;

                return [
                    'uid'                 => 'qi-'.$item->id,
                    'id'                  => $item->id,
                    'equipment_id'        => $item->equipment_id,
                    'product_type_id'     => $catalog?->product_type_id ?? $item->product_type_id,
                    'product_category_id' => $catalog?->product_category_id ?? $item->product_category_id,
                    'product_id'          => $item->product_id,
                    'description'         => $catalog?->name ?? $item->description,
                    'quantity'            => (string) $item->quantity,
                    'unit_price'          => $catalog ? (string) $catalog->sale_price : (string) $item->unit_price,
                    'discount_percentage' => $catalog && (float) $item->discount_percentage > 0
                        ? (string) $catalog->discountPercentage()
                        : '0',
                    'apply_discount'      => $catalog?->hasDiscount() && (float) $item->discount_percentage > 0,
                ];
            })->values()->all();
            $this->syncActiveEquipment();

            return;
        }

        abort_unless(auth()->user()?->can('workshop.quotations.create'), 403);

        $this->form->hours_entry = $this->form->defaultHoursEntry();
    }

    public function updatedFormClientId(): void
    {
        $this->form->equipment_ids = [];
        $this->clearItemEquipmentAssignments();
        $this->syncActiveEquipment();
    }

    public function updatedFormEquipmentIds(): void
    {
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

    /** @param  list<int>  $equipment_ids */
    private function resolveCatalogEquipmentId(array $equipment_ids): ?int
    {
        $equipment_id = $this->active_equipment_id ? (int) $this->active_equipment_id : null;

        if ($equipment_id && in_array($equipment_id, $equipment_ids, true)) {
            return $equipment_id;
        }

        return count($equipment_ids) === 1 ? $equipment_ids[0] : null;
    }

    private function normalizeItemEquipmentIds(): void
    {
        foreach ($this->items as $index => $row) {
            $id = $row['equipment_id'] ?? null;
            $this->items[$index]['equipment_id'] = ($id === '' || $id === null || (int) $id <= 0)
                ? null
                : (int) $id;
        }
    }

    /** @return list<mixed> */
    private function itemEquipmentRule(): array
    {
        $equipment_ids = $this->form->resolvedEquipmentIds();
        $rules = ['nullable', 'integer'];

        if ($equipment_ids !== []) {
            $rules[] = Rule::in($equipment_ids);
        }

        return $rules;
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

    public function addCatalogItem(int $product_id): void
    {
        $equipment_ids = $this->form->resolvedEquipmentIds();
        $equipment_id  = $this->resolveCatalogEquipmentId($equipment_ids);

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
            $same_equipment = (int) ($row['equipment_id'] ?? 0) === (int) ($equipment_id ?? 0);

            if ($same_product && $same_equipment) {
                $this->items[$index]['quantity'] = (string) ((float) $this->items[$index]['quantity'] + $quantity);
                $this->catalog_quantities[$product_id] = '1';

                return;
            }
        }

        $apply_discount = $catalog->hasDiscount() && $this->catalogAppliesDiscount($catalog->id);

        $this->items[] = [
            'uid'                 => uniqid('qi-item-', true),
            'id'                  => null,
            'equipment_id'        => $equipment_id,
            'product_type_id'     => $catalog->product_type_id,
            'product_category_id' => $catalog->product_category_id,
            'product_id'          => $catalog->id,
            'description'         => $catalog->name,
            'quantity'            => (string) $quantity,
            'unit_price'          => (string) $catalog->sale_price,
            'discount_percentage' => $apply_discount ? (string) $catalog->discountPercentage() : '0',
            'apply_discount'      => $apply_discount,
        ];

        $this->catalog_quantities[$product_id] = '1';
    }

    public function changeItemQuantity(int $index, float $delta): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $quantity = (float) ($this->items[$index]['quantity'] ?? 1) + $delta;
        $this->items[$index]['quantity'] = $this->formatQuantity(max(1, $quantity));
    }

    public function updatedItems(mixed $value, string $key): void
    {
        $parts = explode('.', (string) $key);
        $index = (int) ($parts[0] ?? -1);
        $field = $parts[1] ?? null;

        if ($index < 0 || ! isset($this->items[$index]) || $field !== 'apply_discount') {
            return;
        }

        $this->syncItemCatalogDiscount($index);
    }

    public function removeItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    protected function catalogAppliesDiscount(int $product_id): bool
    {
        $value = $this->catalog_apply_discount[$product_id] ?? true;

        return $value === true || $value === 1 || $value === '1';
    }

    protected function syncItemCatalogDiscount(int $index): void
    {
        $product_id = (int) ($this->items[$index]['product_id'] ?? 0);

        if ($product_id <= 0) {
            $this->items[$index]['apply_discount'] = false;
            $this->items[$index]['discount_percentage'] = '0';

            return;
        }

        $product = Product::query()
            ->forAuthUser()
            ->where('business_id', $this->form->resolvedBusinessId())
            ->whereKey($product_id)
            ->first();

        if (! $product?->hasDiscount()) {
            $this->items[$index]['apply_discount'] = false;
            $this->items[$index]['discount_percentage'] = '0';

            return;
        }

        $apply = $this->itemAppliesDiscount($this->items[$index]);
        $this->items[$index]['apply_discount'] = $apply;
        $this->items[$index]['discount_percentage'] = $apply ? (string) $product->discountPercentage() : '0';
        $this->items[$index]['unit_price'] = (string) $product->sale_price;
        $this->items[$index]['description'] = $product->name;
    }

    private function formatQuantity(float $quantity): string
    {
        return rtrim(rtrim(number_format($quantity, 2, '.', ''), '0'), '.') ?: '1';
    }

    /** @return array<int, array<string, mixed>> */
    protected function filledItems(): array
    {
        return array_values(array_filter(
            $this->items,
            fn ($row) => (int) ($row['product_id'] ?? 0) > 0
        ));
    }

    /**
     * Precio y descuento siempre salen del catálogo; el cliente solo decide si aplica el descuento.
     *
     * @param  array<string, mixed>  $row
     * @param  \Illuminate\Support\Collection<int, Product>  $catalog_by_id
     * @return array{quantity: float, unit_price: float, discount_percentage: float, product_category_id: int|null}
     */
    protected function catalogLinePricing(array $row, $catalog_by_id): array
    {
        $product = $catalog_by_id->get((int) ($row['product_id'] ?? 0));
        $apply_discount = $product && $product->hasDiscount() && $this->itemAppliesDiscount($row);

        return [
            'quantity'            => (float) ($row['quantity'] ?? 0),
            'unit_price'          => $product ? (float) $product->sale_price : 0.0,
            'discount_percentage' => $apply_discount ? $product->discountPercentage() : 0.0,
            'product_category_id' => $product?->product_category_id,
        ];
    }

    /** @param  array<string, mixed>  $row */
    protected function itemAppliesDiscount(array $row): bool
    {
        $value = $row['apply_discount'] ?? false;

        return $value === true || $value === 1 || $value === '1';
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();
        $this->advanceToStep(min($this->step + 1, QuotationForm::TOTAL_STEPS));
    }

    public function previousStep(): void
    {
        $this->step = max($this->step - 1, QuotationForm::STEP_GENERAL);
    }

    public function goToStep(int $step): void
    {
        if ($step < QuotationForm::STEP_GENERAL || $step > QuotationForm::TOTAL_STEPS) {
            return;
        }

        if ($step > $this->step) {
            for ($current = $this->step; $current < $step; $current++) {
                if ($current === QuotationForm::STEP_ITEMS) {
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
            auth()->user()->can($this->form->isEditing() ? 'workshop.quotations.edit' : 'workshop.quotations.create'),
            403
        );

        $business_id = $this->form->resolvedBusinessId();
        abort_unless($business_id, 403, 'No tienes un negocio asociado.');

        $this->items = $this->filledItems();

        try {
            if ($this->items !== []) {
                $this->validate($this->itemRules());
            } else {
                $this->validate(['items' => ['required', 'array', 'min:1']]);
            }

            $quotation = CreateOrUpdateQuotationAction::run(
                $business_id,
                $this->form->quotation_id,
                $this->form->client_id,
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
                ? 'Cotización guardada'
                : "Cotización {$quotation->reference} creada",
            'icon'  => 'success',
        ]);

        $this->dispatch('quotation-saved');

        $this->redirectRoute('admin.workshop.quotations.show', $quotation, navigate: true);
    }

    public function deleteQuotation(): void
    {
        abort_unless(auth()->user()?->can('workshop.quotations.delete'), 403);
        abort_unless($this->form->quotation_id, 403);

        $this->askDeleteConfirmation($this->form->quotation_id, '¿Eliminar esta cotización?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            DeleteQuotationAction::run($this->delete_id);
            $this->alertDeleteSuccess('Cotización eliminada correctamente.');
            $this->redirectRoute('admin.workshop.quotations.index', navigate: true);
        } catch (\Throwable $e) {
            $this->alertDeleteError($e->getMessage() ?: 'No se pudo eliminar la cotización.');
        }
    }

    /** @return array<string, mixed> */
    protected function itemRules(): array
    {
        $this->normalizeItemEquipmentIds();

        return [
            'items'                       => ['array'],
            'items.*.equipment_id'        => $this->itemEquipmentRule(),
            'items.*.product_type_id'     => ['required', 'integer', 'exists:product_types,id'],
            'items.*.product_id'          => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'            => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'items.*.equipment_id'    => 'equipo del ítem',
            'items.*.product_type_id' => 'tipo de producto',
            'items.*.product_id'      => 'producto',
            'items.*.quantity'        => 'cantidad',
            'items'                   => 'ítems',
        ];
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return [
            'items.required'              => 'Agrega al menos un ítem a la cotización.',
            'items.min'                   => 'Agrega al menos un ítem a la cotización.',
            'items.*.product_id.required' => 'Selecciona un producto del catálogo.',
        ];
    }

    protected function validateCurrentStep(): void
    {
        if ($this->step === QuotationForm::STEP_ITEMS) {
            $this->validateItemsStep();

            return;
        }

        $this->form->validate($this->form->rulesForStep($this->step));
    }

    protected function validateItemsStep(): void
    {
        $this->items = $this->filledItems();

        if ($this->items === []) {
            $this->validate(['items' => ['required', 'array', 'min:1']]);

            return;
        }

        $this->validate($this->itemRules());
    }

    protected function isFlowComplete(): bool
    {
        return $this->form->quotation_id
            && collect($this->items)->contains(fn ($row) => (int) ($row['product_id'] ?? 0) > 0);
    }

    protected function advanceToStep(int $step): void
    {
        if ($step > $this->step && ! $this->isFlowComplete()) {
            $was_new   = ! $this->form->isEditing();
            $quotation = $this->persistProgress($step);

            if ($was_new) {
                $this->redirectRoute('admin.workshop.quotations.form.edit', $quotation, navigate: true);

                return;
            }
        }

        $this->step = $step;
    }

    protected function persistProgress(int $step): Quotation
    {
        $business_id = $this->form->resolvedBusinessId();
        abort_unless($business_id, 403);

        $quotation = CreateOrUpdateQuotationAction::run(
            $business_id,
            $this->form->quotation_id,
            $this->form->client_id,
            $this->form->resolvedEquipmentIds(),
            $this->form->payload($step),
            $this->items
        );

        $this->form->quotation_id = $quotation->id;
        $this->reference          = $quotation->reference;
        $this->quotation_status   = $quotation->status instanceof QuotationStatus
            ? $quotation->status->value
            : (string) $quotation->status;
        $this->syncWizardProgress($quotation);

        return $quotation;
    }

    protected function clearItemEquipmentAssignments(): void
    {
        foreach ($this->items as $index => $row) {
            $this->items[$index]['equipment_id'] = null;
        }
    }

    /** @return array<string, float> */
    /** @param  \Illuminate\Support\Collection<int, Product>  $catalog_by_id
     *  @return array<string, float>
     */
    protected function previewSubtotals($catalog_by_id): array
    {
        $groups = [
            'mano_obra'   => 0.0,
            'repuestos'   => 0.0,
            'lubricantes' => 0.0,
            'otros'       => 0.0,
        ];

        $category_ids = $catalog_by_id->pluck('product_category_id')->filter()->unique()->values();

        $category_map = ProductCategory::query()
            ->visibleToUser()
            ->whereIn('id', $category_ids)
            ->pluck('name', 'id');

        foreach ($this->items as $row) {
            $line     = $this->catalogLinePricing($row, $catalog_by_id);
            $amount   = round($line['quantity'] * $line['unit_price'] * (1 - $line['discount_percentage'] / 100), 2);
            $category_name = $category_map[$line['product_category_id'] ?? ''] ?? '';

            if ($category_name === 'Mano de Obra') {
                $groups['mano_obra'] += $amount;
            } elseif ($category_name === 'Repuestos') {
                $groups['repuestos'] += $amount;
            } elseif ($category_name === 'Lubricantes y fluidos') {
                $groups['lubricantes'] += $amount;
            } else {
                $groups['otros'] += $amount;
            }
        }

        return array_map(fn ($v) => round($v, 2), $groups);
    }

    public function render()
    {
        $business_id = $this->form->resolvedBusinessId();

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

            if ($catalog_product->hasDiscount()) {
                $this->catalog_apply_discount[$catalog_product->id] ??= true;
            }
        }

        $preview_product = null;
        if ($this->preview_product_id) {
            $this->catalog_quantities[$this->preview_product_id] ??= '1';

            $preview_product = Product::query()
                ->forAuthUser()
                ->where('business_id', $business_id)
                ->with(['images', 'product_type', 'product_category', 'unit', 'brand'])
                ->find($this->preview_product_id);

            if ($preview_product?->hasDiscount()) {
                $this->catalog_apply_discount[$preview_product->id] ??= true;
            }
        }

        $cart_quantities = collect($this->items)
            ->filter(fn ($row) => ! empty($row['product_id']))
            ->groupBy('product_id')
            ->map(fn ($rows) => $rows->sum(fn ($row) => (float) ($row['quantity'] ?? 0)));

        $cart_product_ids = collect($this->items)->pluck('product_id')->filter()->unique()->values()->all();
        $cart_products = $cart_product_ids !== []
            ? Product::query()->forAuthUser()->whereIn('id', $cart_product_ids)->with('images')->get()->keyBy('id')
            : collect();
        $catalog_by_id = $cart_products;

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

        $category_subtotals = $this->previewSubtotals($catalog_by_id);
        $subtotal = array_sum($category_subtotals);
        $applied_tax_lines = AppliedTaxesPreview::lines($this->form->custom_tax_ids, $business_id, $subtotal);
        $tax      = AppliedTaxesPreview::totalAmount($applied_tax_lines);
        $total    = $subtotal + $tax;
        $advance_pct = (float) ($this->form->advance_percentage ?: 0);
        $advance_amount = round($subtotal * ($advance_pct / 100), 2);

        $status_enum = $this->quotation_status
            ? QuotationStatus::tryFrom($this->quotation_status)
            : null;

        $status_label = null;
        $status_badge_class = 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20';

        if ($this->quotation_status) {
            $status_label = Status::query()
                ->forModule('quotations')
                ->where('name', $this->quotation_status)
                ->value('label')
                ?? $status_enum?->label()
                ?? $this->quotation_status;
            $status_badge_class = $status_enum?->badgeClass() ?? $status_badge_class;
        }

        $item_line_totals    = [];
        $item_line_discounts = [];
        $items_discount_total = 0.0;
        foreach ($this->items as $index => $row) {
            $line = $this->catalogLinePricing($row, $catalog_by_id);
            $base = round($line['quantity'] * $line['unit_price'], 2);
            $total_line = round($base * (1 - $line['discount_percentage'] / 100), 2);

            $item_line_totals[$index]    = $total_line;
            $item_line_discounts[$index] = round($base - $total_line, 2);
            $items_discount_total += round($base - $total_line, 2);
        }

        $total_steps = QuotationForm::TOTAL_STEPS;

        return view('livewire.admin.workshop.quotations.form', [
            'is_editing'             => $this->form->isEditing(),
            'step'                   => $this->step,
            'total_steps'            => $total_steps,
            ...$this->wizardProgressViewData($total_steps),
            'steps'                  => [
                QuotationForm::STEP_GENERAL => [
                    'title'       => 'Datos',
                    'description' => 'Cliente y equipos',
                ],
                QuotationForm::STEP_CONDITIONS => [
                    'title'       => 'Condiciones',
                    'description' => 'Vigencia, impuestos y pago',
                ],
                QuotationForm::STEP_ITEMS => [
                    'title'       => 'Ítems',
                    'description' => 'Productos y servicios',
                ],
            ],
            'applied_tax_lines'    => $applied_tax_lines,
            'product_types'        => $product_types,
            'catalog_products'     => $catalog_products,
            'catalog_has_more'     => $catalog_has_more,
            'preview_product'      => $preview_product,
            'cart_quantities'      => $cart_quantities,
            'cart_products'        => $cart_products,
            'catalog_by_id'        => $catalog_by_id,
            'equipment_for_client' => $equipment_for_client,
            'selected_equipments'  => $selected_equipments,
            'category_subtotals'   => $category_subtotals,
            'preview_subtotal'     => $subtotal,
            'preview_tax'          => $tax,
            'preview_total'        => $total,
            'preview_advance_amount' => $advance_amount,
            'item_line_discounts'  => $item_line_discounts,
            'items_discount_total' => $items_discount_total,
            'status_label'         => $status_label,
            'status_badge_class'   => $status_badge_class,
            'item_line_totals'     => $item_line_totals,
            'can_delete'           => $this->saved_complete
                && $this->form->quotation_id
                && auth()->user()->can('workshop.quotations.delete')
                && ! in_array($this->quotation_status, [
                    QuotationStatus::Accepted->value,
                    QuotationStatus::Rejected->value,
                ], true),
            'can_create_ot'        => $this->saved_complete
                && $this->form->quotation_id
                && auth()->user()->can('workshop.work-orders.create')
                && $this->quotation_status === QuotationStatus::Accepted->value
                && ! $this->linked_work_order_id,
        ]);
    }
}
