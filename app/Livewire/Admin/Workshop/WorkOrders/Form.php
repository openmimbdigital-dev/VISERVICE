<?php

namespace App\Livewire\Admin\Workshop\WorkOrders;

use App\Actions\Workshop\CreateOrUpdateWorkOrderAction;
use App\Actions\Workshop\DeleteWorkOrderAction;
use App\Enums\WorkOrderStatus;
use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Livewire\Forms\Admin\Workshop\WorkOrderForm;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Quotation;
use App\Models\WorkOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Orden de Trabajo')]
class Form extends Component
{
    use ConfirmsDeletionWithLivewireAlert;

    public WorkOrderForm $form;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public ?string $reference = null;

    /** Bloquea el select de cotización cuando se abre el form desde una cotización. */
    public bool $quotation_locked = false;

    public function mount(?WorkOrder $workOrder = null): void
    {
        if ($workOrder) {
            abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);

            abort_unless(
                WorkOrder::query()->forAuthUser()->whereKey($workOrder->id)->exists(),
                404
            );

            abort_unless($workOrder->status?->isOpen() ?? false, 403);

            $workOrder->load(['items.productType', 'items.catalogProduct']);
            $this->form->setWorkOrder($workOrder);
            $this->reference = $workOrder->reference;
            $this->quotation_locked = (bool) $workOrder->quotation_id;
            $this->items = $workOrder->items->map(fn ($item) => [
                'uid'                 => 'woi-'.$item->id,
                'id'                  => $item->id,
                'product_type_id'     => $item->product_type_id,
                'product_id'          => $item->product_id,
                'description'         => $item->description,
                'quantity'            => (string) $item->quantity,
                'unit_price'          => (string) $item->unit_price,
                'discount_percentage' => (string) $item->discount_percentage,
            ])->values()->all();

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
    }

    public function updatedFormClientId(): void
    {
        if ($this->form->quotation_id) {
            return;
        }

        $this->form->equipment_id = null;
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
            ->where('status', \App\Enums\QuotationStatus::Accepted)
            ->where(function ($query) {
                $query->whereDoesntHave('workOrder');

                if ($this->form->work_order_id) {
                    $query->orWhereHas('workOrder', fn ($q) => $q->whereKey($this->form->work_order_id));
                }
            })
            ->with('items')
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
            'product_type_id'     => $item->product_type_id,
            'product_id'          => $item->product_id,
            'description'         => $item->description,
            'quantity'            => (string) $item->quantity,
            'unit_price'          => (string) $item->unit_price,
            'discount_percentage' => (string) $item->discount_percentage,
        ])->values()->all();
    }

    public function updatedItems(mixed $value, string $key): void
    {
        // Livewire pasa $key como "{index}.product_id" (ej. "1.product_id").
        if (! str_ends_with((string) $key, '.product_id') || ! $value) {
            return;
        }

        $index = (int) explode('.', (string) $key)[0];
        $catalog = Product::query()
            ->forAuthUser()
            ->where('business_id', $this->form->resolvedBusinessId())
            ->whereKey($value)
            ->first();

        if (! $catalog || ! isset($this->items[$index])) {
            return;
        }

        $this->items[$index]['product_type_id'] = $catalog->product_type_id;
        $this->items[$index]['description']     = $catalog->name;
        $this->items[$index]['unit_price']      = (string) $catalog->sale_price;
    }

    public function addItem(): void
    {
        $this->items[] = [
            'uid'                 => uniqid('wo-item-', true),
            'id'                  => null,
            'product_type_id'     => null,
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
            auth()->user()->can($this->form->isEditing() ? 'workshop.work-orders.edit' : 'workshop.work-orders.create'),
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

        $work_order = CreateOrUpdateWorkOrderAction::run(
            $business_id,
            $this->form->work_order_id,
            (int) $this->form->client_id,
            (int) $this->form->equipment_id,
            $this->form->validated(),
            $this->items
        );

        $this->dispatch('swal', [
            'title' => $this->form->isEditing()
                ? 'Orden de trabajo guardada'
                : "OT {$work_order->reference} creada",
            'icon'  => 'success',
        ]);

        $this->redirectRoute('admin.workshop.work-orders.show', $work_order->id, navigate: true);
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

    /** @return array<string, mixed> */
    protected function itemRules(): array
    {
        return [
            'items'                       => ['array'],
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
            'items.*.description' => 'descripción del ítem',
            'items.*.quantity'    => 'cantidad',
            'items.*.unit_price'  => 'precio unitario',
        ];
    }

    public function render()
    {
        $business_id = $this->form->resolvedBusinessId();
        $from_quotation = (bool) $this->form->quotation_id;

        $clients = Client::query()->forAuthUser()->where('status', true)->orderBy('name')->get();
        $accepted_quotations = $this->form->getAcceptedQuotations();
        $product_types = ProductType::query()->visibleToUser()->where('active', true)->orderBy('name')->get();
        $catalog_products = Product::query()->forAuthUser()->where('business_id', $business_id)->active()->orderBy('name')->get();

        $equipment_for_client = $this->form->client_id
            ? Equipment::query()->forAuthUser()
                ->where('client_id', $this->form->client_id)
                ->where('status', true)
                ->orderBy('name')
                ->orderBy('plate')
                ->get(['id', 'name', 'brand_name', 'plate'])
            : collect();

        $subtotal = 0.0;
        foreach ($this->items as $row) {
            $qty      = (float) ($row['quantity'] ?? 0);
            $price    = (float) ($row['unit_price'] ?? 0);
            $discount = (float) ($row['discount_percentage'] ?? 0);
            $subtotal += round($qty * $price * (1 - $discount / 100), 2);
        }
        $tax_pct = (float) ($this->form->tax_percentage ?: 0);
        $tax     = round($subtotal * ($tax_pct / 100), 2);
        $total   = $subtotal + $tax;

        return view('livewire.admin.workshop.work-orders.form', [
            'is_editing'           => $this->form->isEditing(),
            'from_quotation'       => $from_quotation,
            'clients'              => $clients,
            'accepted_quotations'  => $accepted_quotations,
            'product_types'        => $product_types,
            'catalog_products'     => $catalog_products,
            'equipment_for_client' => $equipment_for_client,
            'preview_subtotal'     => $subtotal,
            'preview_tax'          => $tax,
            'preview_total'        => $total,
            'can_delete'           => $this->form->work_order_id
                && auth()->user()->can('workshop.work-orders.delete'),
        ]);
    }
}
