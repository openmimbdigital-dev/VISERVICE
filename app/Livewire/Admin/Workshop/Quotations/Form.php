<?php

namespace App\Livewire\Admin\Workshop\Quotations;

use App\Actions\Workshop\CreateOrUpdateQuotationAction;
use App\Actions\Workshop\DeleteQuotationAction;
use App\Enums\QuotationStatus;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Forms\Admin\Workshop\QuotationForm;
use App\Models\CustomTax;
use App\Models\Equipment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\Quotation;
use App\Models\Status;
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

    public QuotationForm $form;

    public int $step = QuotationForm::STEP_GENERAL;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public ?string $reference = null;

    public ?string $quotation_status = null;

    public ?int $linked_work_order_id = null;

    public ?string $linked_work_order_reference = null;

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
                    'id'                  => $item->id,
                    'equipment_id'        => $item->equipment_id,
                    'product_type_id'     => $catalog?->product_type_id ?? $item->product_type_id,
                    'product_category_id' => $catalog?->product_category_id ?? $item->product_category_id,
                    'product_id'          => $item->product_id,
                    'description'         => $catalog?->name ?? $item->description,
                    'quantity'            => (string) $item->quantity,
                    'unit_price'          => $catalog ? (string) $catalog->sale_price : (string) $item->unit_price,
                    'discount_percentage' => $catalog ? (string) $catalog->discountPercentage() : '0',
                    'apply_discount'      => $catalog?->hasDiscount() && (float) $item->discount_percentage > 0,
                ];
            })->values()->all();

            return;
        }

        abort_unless(auth()->user()?->can('workshop.quotations.create'), 403);

        $this->form->hours_entry = $this->form->defaultHoursEntry();
    }

    public function updatedFormClientId(): void
    {
        $this->form->equipment_ids = [];
        $this->clearItemEquipmentAssignments();
    }

    public function updatedFormCustomTaxId(mixed $value): void
    {
        if (! $value) {
            $this->form->tax_percentage = '0';

            return;
        }

        $custom_tax = CustomTax::query()
            ->forAuthUser()
            ->where('business_id', $this->form->resolvedBusinessId())
            ->whereKey($value)
            ->first();

        if ($custom_tax) {
            $this->form->tax_percentage = (string) $custom_tax->percentage;
        }
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
    }

    public function updatedItems(mixed $value, string $key): void
    {
        $parts = explode('.', (string) $key);
        $index = (int) ($parts[0] ?? -1);
        $field = $parts[1] ?? null;

        if ($index < 0 || ! isset($this->items[$index]) || $field === null) {
            return;
        }

        if ($field === 'product_type_id') {
            $this->resetItemCatalogSelection($index);

            return;
        }

        if ($field !== 'product_id') {
            return;
        }

        if (! $value) {
            $this->resetItemCatalogPricing($index);

            return;
        }

        $this->applyCatalogProduct($index, (int) $value);
    }

    public function addItem(): void
    {
        $equipment_ids = $this->form->resolvedEquipmentIds();

        $this->items[] = [
            'id'                  => null,
            'equipment_id'        => count($equipment_ids) === 1 ? $equipment_ids[0] : null,
            'product_type_id'     => null,
            'product_category_id' => null,
            'product_id'          => null,
            'description'         => '',
            'quantity'            => '1',
            'unit_price'          => '0',
            'discount_percentage' => '0',
            'apply_discount'      => false,
        ];
    }

    public function removeItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    protected function applyCatalogProduct(int $index, int $product_id): void
    {
        $catalog = Product::query()
            ->forAuthUser()
            ->complete()
            ->where('business_id', $this->form->resolvedBusinessId())
            ->whereKey($product_id)
            ->first();

        if (! $catalog) {
            $this->resetItemCatalogSelection($index);

            return;
        }

        $selected_type = $this->items[$index]['product_type_id'] ?? null;
        if ($selected_type && (int) $catalog->product_type_id !== (int) $selected_type) {
            $this->resetItemCatalogSelection($index);

            return;
        }

        $this->items[$index]['product_id']          = $catalog->id;
        $this->items[$index]['product_type_id']     = $catalog->product_type_id;
        $this->items[$index]['product_category_id'] = $catalog->product_category_id;
        $this->items[$index]['description']         = $catalog->name;
        $this->items[$index]['unit_price']          = (string) $catalog->sale_price;
        $this->items[$index]['discount_percentage'] = (string) $catalog->discountPercentage();
        $this->items[$index]['apply_discount']      = $catalog->hasDiscount();
    }

    protected function resetItemCatalogSelection(int $index): void
    {
        $this->items[$index]['product_id'] = null;
        $this->resetItemCatalogPricing($index);
    }

    protected function resetItemCatalogPricing(int $index): void
    {
        $this->items[$index]['product_category_id'] = null;
        $this->items[$index]['description']         = '';
        $this->items[$index]['unit_price']          = '0';
        $this->items[$index]['discount_percentage'] = '0';
        $this->items[$index]['apply_discount']      = false;
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

        $this->redirectRoute('admin.workshop.quotations.index', navigate: true);
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
        $equipment_ids = $this->form->resolvedEquipmentIds();

        return [
            'items'                       => ['array'],
            'items.*.equipment_id'        => ['required', 'integer', Rule::in($equipment_ids)],
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

        $selected_custom_tax = $this->form->custom_tax_id
            ? CustomTax::query()
                ->forAuthUser()
                ->where('business_id', $business_id)
                ->whereKey($this->form->custom_tax_id)
                ->first()
            : null;
        $product_types = ProductType::query()->visibleToUser()->where('active', true)->orderBy('name')->get();
        $selected_product_ids = collect($this->items)->pluck('product_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        $catalog_products = Product::query()
            ->forAuthUser()
            ->complete()
            ->where('business_id', $business_id)
            ->where(function ($query) use ($selected_product_ids) {
                $query->active();
                if ($selected_product_ids !== []) {
                    $query->orWhereIn('products.id', $selected_product_ids);
                }
            })
            ->orderBy('name')
            ->get();
        $catalog_by_id = $catalog_products->keyBy('id');

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
        $tax_pct  = $selected_custom_tax ? (float) $selected_custom_tax->percentage : 0;
        $tax      = round($subtotal * ($tax_pct / 100), 2);
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

        $item_line_totals = [];
        foreach ($this->items as $index => $row) {
            $line = $this->catalogLinePricing($row, $catalog_by_id);
            $item_line_totals[$index] = round(
                $line['quantity'] * $line['unit_price'] * (1 - $line['discount_percentage'] / 100),
                2
            );
        }

        $total_steps   = QuotationForm::TOTAL_STEPS;
        $progress      = (int) round(($this->step / $total_steps) * 100);
        $radius        = 30;
        $circumference = round(2 * M_PI * $radius, 2);

        return view('livewire.admin.workshop.quotations.form', [
            'is_editing'             => $this->form->isEditing(),
            'step'                   => $this->step,
            'total_steps'            => $total_steps,
            'progress'               => $progress,
            'progress_circumference' => $circumference,
            'progress_offset'        => round($circumference * (1 - $progress / 100), 2),
            'steps'                  => [
                QuotationForm::STEP_GENERAL => [
                    'title'       => 'Datos',
                    'description' => 'Cliente y equipos',
                ],
                QuotationForm::STEP_CONDITIONS => [
                    'title'       => 'Condiciones',
                    'description' => 'Vigencia, impuesto y pago',
                ],
                QuotationForm::STEP_ITEMS => [
                    'title'       => 'Ítems',
                    'description' => 'Productos y servicios',
                ],
            ],
            'selected_custom_tax'  => $selected_custom_tax,
            'product_types'        => $product_types,
            'catalog_products'     => $catalog_products,
            'catalog_by_id'        => $catalog_by_id,
            'equipment_for_client' => $equipment_for_client,
            'selected_equipments'  => $selected_equipments,
            'category_subtotals'   => $category_subtotals,
            'preview_subtotal'     => $subtotal,
            'preview_tax'          => $tax,
            'preview_total'        => $total,
            'preview_advance_amount' => $advance_amount,
            'status_label'         => $status_label,
            'status_badge_class'   => $status_badge_class,
            'item_line_totals'     => $item_line_totals,
            'can_delete'           => $this->form->quotation_id
                && auth()->user()->can('workshop.quotations.delete')
                && ! in_array($this->quotation_status, [
                    QuotationStatus::Accepted->value,
                    QuotationStatus::Rejected->value,
                ], true),
            'can_create_ot'        => $this->form->quotation_id
                && auth()->user()->can('workshop.work-orders.create')
                && $this->quotation_status === QuotationStatus::Accepted->value
                && ! $this->linked_work_order_id,
        ]);
    }
}
