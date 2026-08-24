<?php

namespace App\Livewire\Admin\Workshop\Quotations;

use App\Actions\Workshop\CreateOrUpdateQuotationAction;
use App\Actions\Workshop\DeleteQuotationAction;
use App\Enums\QuotationStatus;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Forms\Admin\Workshop\QuotationForm;
use App\Models\BusinessBankAccount;
use App\Models\BusinessPaymentMethod;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\Quotation;
use App\Models\QuotationServiceType;
use App\Models\Status;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cotización')]
class Form extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public QuotationForm $form;

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

            $quotation->load(['items.productType', 'items.productCategory', 'equipments:id', 'workOrder']);

            if (! $quotation->isEditable()) {
                $this->redirectRoute('admin.workshop.quotations.show', $quotation, navigate: true);

                return;
            }

            $this->form->setQuotation($quotation);
            $this->reference = $quotation->reference;
            $this->quotation_status = $quotation->status instanceof QuotationStatus
                ? $quotation->status->value
                : (string) $quotation->status;
            $this->linked_work_order_id = $quotation->workOrder?->id;
            $this->linked_work_order_reference = $quotation->workOrder?->reference;
            $this->items = $quotation->items->map(fn ($item) => [
                'id'                  => $item->id,
                'equipment_id'        => $item->equipment_id,
                'product_type_id'     => $item->product_type_id,
                'product_category_id' => $item->product_category_id,
                'product_id'          => $item->product_id,
                'description'         => $item->description,
                'quantity'            => (string) $item->quantity,
                'unit_price'          => (string) $item->unit_price,
                'discount_percentage' => (string) $item->discount_percentage,
            ])->values()->all();

            return;
        }

        abort_unless(auth()->user()?->can('workshop.quotations.create'), 403);
    }

    public function updatedFormClientId(): void
    {
        $this->form->equipment_ids = [];
        $this->clearItemEquipmentAssignments();
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
            $this->items[$index]['product_id'] = null;

            return;
        }

        if ($field !== 'product_id' || ! $value) {
            return;
        }

        $catalog = Product::query()
            ->forAuthUser()
            ->where('business_id', $this->form->resolvedBusinessId())
            ->whereKey($value)
            ->first();

        if (! $catalog) {
            return;
        }

        $selected_type = $this->items[$index]['product_type_id'] ?? null;
        if ($selected_type && (int) $catalog->product_type_id !== (int) $selected_type) {
            $this->items[$index]['product_id'] = null;

            return;
        }

        $this->items[$index]['product_type_id']     = $catalog->product_type_id;
        $this->items[$index]['product_category_id'] = $catalog->product_category_id;
        $this->items[$index]['description']         = $catalog->name;
        $this->items[$index]['unit_price']          = (string) $catalog->sale_price;
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

    public function save(): void
    {
        abort_unless(
            auth()->user()->can($this->form->isEditing() ? 'workshop.quotations.edit' : 'workshop.quotations.create'),
            403
        );

        $business_id = $this->form->resolvedBusinessId();
        abort_unless($business_id, 403, 'No tienes un negocio asociado.');

        $this->items = array_values(array_filter(
            $this->items,
            fn ($row) => trim((string) ($row['description'] ?? '')) !== ''
        ));

        if ($this->items !== []) {
            $this->validate($this->itemRules());
        }

        $quotation = CreateOrUpdateQuotationAction::run(
            $business_id,
            $this->form->quotation_id,
            $this->form->client_id,
            $this->form->resolvedEquipmentIds(),
            $this->form->validated(),
            $this->items
        );

        $this->dispatch('swal', [
            'title' => $this->form->isEditing()
                ? 'Cotización guardada'
                : "Cotización {$quotation->reference} creada",
            'icon'  => 'success',
        ]);

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
            'items.*.description'         => ['required', 'string', 'max:200'],
            'items.*.quantity'            => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'          => ['required', 'numeric', 'min:0'],
            'items.*.discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.product_type_id'     => ['nullable', 'integer', 'exists:product_types,id'],
            'items.*.product_category_id' => ['nullable', 'integer', 'exists:product_categories,id'],
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
        ];
    }

    protected function clearItemEquipmentAssignments(): void
    {
        foreach ($this->items as $index => $row) {
            $this->items[$index]['equipment_id'] = null;
        }
    }

    /** @return array<string, float> */
    protected function previewSubtotals(): array
    {
        $groups = [
            'mano_obra'   => 0.0,
            'repuestos'   => 0.0,
            'lubricantes' => 0.0,
            'otros'       => 0.0,
        ];

        $category_map = ProductCategory::query()
            ->visibleToUser()
            ->whereIn('id', collect($this->items)->pluck('product_category_id')->filter())
            ->pluck('name', 'id');

        foreach ($this->items as $row) {
            $qty      = (float) ($row['quantity'] ?? 0);
            $price    = (float) ($row['unit_price'] ?? 0);
            $discount = (float) ($row['discount_percentage'] ?? 0);
            $amount   = round($qty * $price * (1 - $discount / 100), 2);

            $category_name = $category_map[$row['product_category_id'] ?? ''] ?? '';

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

        $clients = Client::query()->forAuthUser()->where('status', true)->orderBy('name')->get();
        $service_types = QuotationServiceType::query()->visibleToUser()->where('active', true)->orderBy('name')->get();
        $payment_methods = BusinessPaymentMethod::query()->visibleToUser()->where('active', true)->orderBy('sort_order')->get();
        $bank_accounts = BusinessBankAccount::query()->forAuthUser()->where('business_id', $business_id)->where('active', true)->get();
        $product_types = ProductType::query()->visibleToUser()->where('active', true)->orderBy('name')->get();
        $catalog_products = Product::query()->forAuthUser()->where('business_id', $business_id)->active()->orderBy('name')->get();

        $equipment_query = Equipment::query()->forAuthUser()
            ->where('client_id', $this->form->client_id)
            ->orderBy('name')
            ->orderBy('plate');

        if ($this->form->resolvedEquipmentIds() !== []) {
            $equipment_query->where(function ($q) {
                $q->where('status', true)
                    ->orWhereIn('id', $this->form->resolvedEquipmentIds());
            });
        } else {
            $equipment_query->where('status', true);
        }

        $equipment_for_client = $this->form->client_id
            ? $equipment_query->get(['id', 'name', 'brand_name', 'plate'])
            : collect();

        $selected_equipment_ids = $this->form->resolvedEquipmentIds();
        $selected_equipments = $equipment_for_client
            ->whereIn('id', $selected_equipment_ids)
            ->values();

        $category_subtotals = $this->previewSubtotals();
        $subtotal = array_sum($category_subtotals);
        $tax_pct  = (float) ($this->form->tax_percentage ?: 0);
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
            $item_line_totals[$index] = round(
                (float) ($row['quantity'] ?? 0)
                * (float) ($row['unit_price'] ?? 0)
                * (1 - (float) ($row['discount_percentage'] ?? 0) / 100),
                2
            );
        }

        return view('livewire.admin.workshop.quotations.form', [
            'is_editing'           => $this->form->isEditing(),
            'clients'              => $clients,
            'service_types'        => $service_types,
            'payment_methods'      => $payment_methods,
            'bank_accounts'        => $bank_accounts,
            'product_types'        => $product_types,
            'catalog_products'     => $catalog_products,
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
