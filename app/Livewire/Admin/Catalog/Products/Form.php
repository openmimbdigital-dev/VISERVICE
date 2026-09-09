<?php

namespace App\Livewire\Admin\Catalog\Products;

use App\Actions\Catalog\CreateOrUpdateProductAction;
use App\Livewire\Concerns\TracksWizardProgress;
use App\Livewire\Forms\Admin\Catalog\ProductForm;
use App\Models\Business;
use App\Models\Product;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Producto')]
class Form extends Component
{
    use TracksWizardProgress;

    public ProductForm $form;

    public int $step = ProductForm::STEP_GENERAL;

    public function mount(?Product $product = null): void
    {
        if ($product?->exists) {
            abort_unless(auth()->user()->can('catalog.products.edit'), 403);

            abort_unless(
                Product::query()->forAuthUser()->whereKey($product->id)->exists(),
                404
            );

            abort_unless($product->isEditableBy(), 403);

            $this->form->setProduct($product);
            $this->syncWizardProgress($product);
            $this->step = $product->isComplete()
                ? ProductForm::STEP_GENERAL
                : max(ProductForm::STEP_GENERAL, min((int) $product->step, ProductForm::TOTAL_STEPS));

            return;
        }

        abort_unless(auth()->user()->can('catalog.products.create'), 403);

        if (! auth()->user()->hasRole('superAdmin')) {
            $this->form->business_id = auth()->user()->business_id;
        }
    }

    public function updatedFormSku(): void
    {
        $this->form->normalizeIdentifiers();
        $this->form->validateOnly('sku');
    }

    public function updatedFormBarcode(): void
    {
        $this->form->normalizeIdentifiers();
        $this->form->validateOnly('barcode');
    }

    public function updatedFormCostPrice(): void
    {
        $this->form->recalculateSalePrice();
    }

    public function updatedFormProfitPercentage(): void
    {
        $this->form->recalculateSalePrice();
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();
        $this->advanceToStep(min($this->step + 1, ProductForm::TOTAL_STEPS));
    }

    public function previousStep(): void
    {
        $this->step = max($this->step - 1, ProductForm::STEP_GENERAL);
    }

    public function goToStep(int $step): void
    {
        if ($step < ProductForm::STEP_GENERAL || $step > ProductForm::TOTAL_STEPS) {
            return;
        }

        if ($step > $this->step) {
            for ($current = $this->step; $current < $step; $current++) {
                $this->form->normalizeIdentifiers();
                $this->form->validate($this->form->rulesForStep($current));
            }
        }

        $this->advanceToStep($step);
    }

    public function save(): void
    {
        abort_unless(
            $this->form->isEditing()
                ? auth()->user()->can('catalog.products.edit')
                : auth()->user()->can('catalog.products.create'),
            403
        );

        $was_editing = $this->form->isEditing();

        try {
            $this->form->normalizeIdentifiers();
            $product = CreateOrUpdateProductAction::run(
                $this->form->product_id,
                $this->form->validated()
            );
        } catch (ValidationException $exception) {
            $this->step = $this->form->firstStepWithErrors($exception->errors());

            throw $exception;
        }

        $this->dispatch('swal', [
            'title' => $was_editing ? 'Producto actualizado' : 'Producto creado',
            'icon'  => 'success',
        ]);

        $this->dispatch('product-saved');

        $this->redirectRoute(
            $was_editing ? 'admin.catalog.products.show' : 'admin.catalog.products.index',
            $was_editing ? ['product' => $product] : [],
            navigate: true
        );
    }

    public function render()
    {
        $is_super_admin = auth()->user()->hasRole('superAdmin');
        $total_steps    = ProductForm::TOTAL_STEPS;

        return view('livewire.admin.catalog.products.form', [
            'is_editing'              => $this->form->isEditing(),
            'is_super_admin'          => $is_super_admin,
            'step'                    => $this->step,
            'total_steps'             => $total_steps,
            ...$this->wizardProgressViewData($total_steps),
            'steps'                   => [
                ProductForm::STEP_GENERAL => [
                    'title'       => 'Información general',
                    'description' => 'SKU, código de barras y datos básicos',
                ],
                ProductForm::STEP_CLASSIFICATION => [
                    'title'       => 'Clasificación',
                    'description' => 'Tipo, categoría, unidad y marca',
                ],
                ProductForm::STEP_PRICING => [
                    'title'       => 'Precios',
                    'description' => 'Costo, porcentaje y precio de venta',
                ],
                ProductForm::STEP_IMAGES => [
                    'title'       => 'Imágenes',
                    'description' => 'Fotos del producto (opcional)',
                ],
            ],
            'businesses'         => $is_super_admin
                ? Business::where('status', true)->orderBy('name')->get(['id', 'name'])
                : collect(),
            'product_types'      => $this->form->getProductTypes(),
            'product_categories' => $this->form->getProductCategories(),
            'units'              => $this->form->getUnits(),
            'brands'             => $this->form->getBrands(),
            'product'            => $this->form->product_id ? Product::find($this->form->product_id) : null,
            'discount_preview'   => $this->discountPreview(),
        ]);
    }

    /**
     * Cómo queda el precio con el descuento capturado, para verlo antes de guardar.
     *
     * @return array{sale_price: float, discount: float, final: float}|null
     */
    private function discountPreview(): ?array
    {
        if ($this->form->discount_type === '' || ! is_numeric($this->form->discount_value)) {
            return null;
        }

        $preview = new Product([
            'sale_price'     => $this->form->sale_price === '' ? 0 : (float) $this->form->sale_price,
            'discount_type'  => $this->form->discount_type,
            'discount_value' => (float) $this->form->discount_value,
        ]);

        if (! $preview->hasDiscount()) {
            return null;
        }

        return [
            'sale_price' => (float) $preview->sale_price,
            'discount'   => $preview->discountAmount(),
            'final'      => $preview->finalPrice(),
        ];
    }

    private function advanceToStep(int $step): void
    {
        if ($step > $this->step && ! $this->form->isFlowComplete()) {
            $was_new = ! $this->form->isEditing();
            $product = $this->persistProgress($step);

            if ($was_new) {
                $this->redirectRoute('admin.catalog.products.edit', ['product' => $product], navigate: true);

                return;
            }
        }

        $this->step = $step;
    }

    private function persistProgress(int $step): Product
    {
        $product = CreateOrUpdateProductAction::run(
            $this->form->product_id,
            $this->form->payload($step)
        );

        $this->form->product_id = $product->id;
        $this->syncWizardProgress($product);

        return $product;
    }

    private function validateCurrentStep(): void
    {
        $this->form->normalizeIdentifiers();

        if ($this->step === ProductForm::STEP_PRICING) {
            $this->form->recalculateSalePrice();
        }

        $this->form->validate($this->form->rulesForStep($this->step));
    }
}
