<?php

namespace App\Livewire\Admin\Workshop\Quotations;

use App\Actions\Workshop\CreateOrUpdateQuotationAction;
use App\Actions\Workshop\DeleteQuotationAction;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Forms\Admin\Workshop\QuotationForm;
use App\Models\BusinessBankAccount;
use App\Models\BusinessPaymentMethod;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemType;
use App\Models\Quotation;
use App\Models\QuotationServiceType;
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

    public function mount(?Quotation $quotation = null): void
    {
        if ($quotation) {
            abort_unless(auth()->user()?->can('workshop.quotations.edit'), 403);

            abort_unless(
                Quotation::query()->forAuthUser()->whereKey($quotation->id)->exists(),
                404
            );

            $quotation->load(['items.itemType', 'items.itemCategory']);
            $this->form->setQuotation($quotation);
            $this->reference = $quotation->reference;
            $this->items = $quotation->items->map(fn ($item) => [
                'id'                  => $item->id,
                'item_type_id'        => $item->item_type_id,
                'item_category_id'    => $item->item_category_id,
                'item_id'             => $item->item_id,
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
        $this->form->equipment_id = null;
    }

    public function updatedItems(mixed $value, string $key): void
    {
        if (! str_ends_with($key, '.item_id') || ! $value) {
            return;
        }

        $index = (int) explode('.', $key)[1];
        $catalog = Item::query()
            ->forAuthUser()
            ->where('business_id', $this->form->resolvedBusinessId())
            ->whereKey($value)
            ->first();

        if (! $catalog || ! isset($this->items[$index])) {
            return;
        }

        $this->items[$index]['item_type_id']     = $catalog->item_type_id;
        $this->items[$index]['item_category_id'] = $catalog->item_category_id;
        $this->items[$index]['description']      = $catalog->name;
        $this->items[$index]['unit_price']       = (string) $catalog->sale_price;
    }

    public function addItem(): void
    {
        $this->items[] = [
            'id'                  => null,
            'item_type_id'        => null,
            'item_category_id'    => null,
            'item_id'             => null,
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
            $this->form->equipment_id,
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
        return [
            'items'                       => ['array'],
            'items.*.description'         => ['required', 'string', 'max:200'],
            'items.*.quantity'            => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'          => ['required', 'numeric', 'min:0'],
            'items.*.discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.item_type_id'        => ['nullable', 'integer', 'exists:item_types,id'],
            'items.*.item_category_id'    => ['nullable', 'integer', 'exists:item_categories,id'],
            'items.*.item_id'             => ['nullable', 'integer', 'exists:items,id'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'items.*.description' => 'descripción del ítem',
            'items.*.quantity'    => 'cantidad',
            'items.*.unit_price'  => 'precio unitario',
        ];
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

        $category_map = ItemCategory::query()
            ->visibleToUser()
            ->whereIn('id', collect($this->items)->pluck('item_category_id')->filter())
            ->pluck('name', 'id');

        foreach ($this->items as $row) {
            $qty      = (float) ($row['quantity'] ?? 0);
            $price    = (float) ($row['unit_price'] ?? 0);
            $discount = (float) ($row['discount_percentage'] ?? 0);
            $amount   = round($qty * $price * (1 - $discount / 100), 2);

            $category_name = $category_map[$row['item_category_id'] ?? ''] ?? '';

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
        $item_types = ItemType::query()->visibleToUser()->where('active', true)->orderBy('name')->get();
        $item_categories = ItemCategory::query()->visibleToUser()->where('active', true)->orderBy('name')->get();
        $catalog_items = Item::query()->forAuthUser()->where('business_id', $business_id)->active()->orderBy('name')->get();

        $equipment_for_client = $this->form->client_id
            ? Equipment::query()->forAuthUser()
                ->where('client_id', $this->form->client_id)
                ->where('status', true)
                ->orderBy('name')
                ->orderBy('plate')
                ->get(['id', 'name', 'brand_name', 'plate'])
            : collect();

        $category_subtotals = $this->previewSubtotals();
        $subtotal = array_sum($category_subtotals);
        $tax_pct  = (float) ($this->form->tax_percentage ?: 0);
        $tax      = round($subtotal * ($tax_pct / 100), 2);
        $total    = $subtotal + $tax;

        return view('livewire.admin.workshop.quotations.form', [
            'is_editing'           => $this->form->isEditing(),
            'clients'              => $clients,
            'service_types'        => $service_types,
            'payment_methods'      => $payment_methods,
            'bank_accounts'        => $bank_accounts,
            'item_types'           => $item_types,
            'item_categories'      => $item_categories,
            'catalog_items'        => $catalog_items,
            'equipment_for_client' => $equipment_for_client,
            'category_subtotals'   => $category_subtotals,
            'preview_subtotal'     => $subtotal,
            'preview_tax'          => $tax,
            'preview_total'        => $total,
            'can_delete'           => $this->form->quotation_id
                && auth()->user()->can('workshop.quotations.delete'),
        ]);
    }
}
