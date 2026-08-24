<?php

namespace App\Livewire\Forms\Admin\Workshop;

use App\Enums\QuotationStatus;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\Quotation;
use App\Models\WorkOrder;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class WorkOrderForm extends Form
{
    public ?int $work_order_id = null;

    public ?int $quotation_id = null;

    public ?int $client_id = null;

    /** @var list<int|string> */
    public array $equipment_ids = [];

    public string $diagnosis = '';

    public string $estimated_delivery = '';

    public string $tax_percentage = '19';

    public string $advance_percentage = '0';

    public string $advance_amount = '0';

    public string $notes = '';

    public string $observations = '';

    public function setWorkOrder(WorkOrder $work_order): void
    {
        $work_order->loadMissing('equipments:id');

        $this->work_order_id       = $work_order->id;
        $this->quotation_id        = $work_order->quotation_id;
        $this->client_id           = $work_order->client_id;
        $this->equipment_ids       = $work_order->equipments->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $this->diagnosis           = $work_order->diagnosis ?? '';
        $this->estimated_delivery  = $work_order->estimated_delivery?->format('Y-m-d') ?? '';
        $this->tax_percentage      = (string) $work_order->tax_percentage;
        $this->advance_percentage  = (string) ($work_order->advance_percentage ?? 0);
        $this->advance_amount      = (string) ($work_order->advance_amount ?? 0);
        $this->notes               = $work_order->notes ?? '';
        $this->observations        = $work_order->observations ?? '';
    }

    public function applyQuotation(Quotation $quotation): void
    {
        $quotation->loadMissing('equipments:id');

        $this->quotation_id = $quotation->id;
        $this->client_id    = $quotation->client_id;
        $this->equipment_ids = $quotation->equipments->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $this->diagnosis    = $quotation->diagnosis ?? '';
        $this->tax_percentage = (string) ($quotation->tax_percentage ?? 19);
        $this->advance_percentage = (string) ($quotation->advance_percentage ?? 0);
        $this->advance_amount = (string) ($quotation->advance_amount ?? 0);
        $this->notes          = $quotation->notes ?? '';
        $this->observations   = $quotation->observations ?? '';
    }

    public function isEditing(): bool
    {
        return (bool) $this->work_order_id;
    }

    public function resolvedBusinessId(): int
    {
        return (int) auth()->user()->business_id;
    }

    /** @return list<int> */
    public function resolvedEquipmentIds(): array
    {
        return array_values(array_unique(array_filter(
            array_map(fn ($id) => (int) $id, $this->equipment_ids),
            fn (int $id) => $id > 0
        )));
    }

    public function rules(): array
    {
        $business_id = $this->resolvedBusinessId();

        return [
            'quotation_id' => [
                'nullable',
                'integer',
                Rule::exists('quotations', 'id')->where(fn ($q) => $q
                    ->where('business_id', $business_id)
                    ->where('status', QuotationStatus::Accepted->value)
                    ->whereNull('deleted_at')),
            ],
            'client_id' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')->where(fn ($q) => $q
                    ->where('business_id', $business_id)
                    ->whereNull('deleted_at')),
            ],
            'equipment_ids' => ['required', 'array', 'min:1'],
            'equipment_ids.*' => [
                'integer',
                Rule::exists('equipment', 'id')->where(fn ($q) => $q
                    ->where('business_id', $business_id)
                    ->where('client_id', $this->client_id)
                    ->whereNull('deleted_at')),
            ],
            'diagnosis'          => ['nullable', 'string'],
            'estimated_delivery' => ['nullable', 'date'],
            'tax_percentage'     => ['required', 'numeric', 'min:0', 'max:100'],
            'advance_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'notes'              => ['nullable', 'string'],
            'observations'       => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required'          => 'Selecciona un cliente.',
            'client_id.exists'            => 'El cliente seleccionado no es válido.',
            'equipment_ids.required'      => 'Selecciona al menos un equipo.',
            'equipment_ids.min'           => 'Selecciona al menos un equipo.',
            'equipment_ids.*.exists'      => 'Uno o más equipos no son válidos para el cliente.',
            'quotation_id.exists'         => 'La cotización debe existir, pertenecer al negocio y estar aceptada.',
            'tax_percentage.required'     => 'Indica el porcentaje de IVA.',
            'advance_percentage.required' => 'Indica el porcentaje de anticipo.',
            'advance_percentage.numeric'  => 'El anticipo debe ser un número.',
            'advance_percentage.min'      => 'El anticipo no puede ser negativo.',
            'advance_percentage.max'      => 'El anticipo no puede superar el 100%.',
            'estimated_delivery.date'     => 'La fecha de entrega estimada no es válida.',
        ];
    }

    /** @return array<string, mixed> */
    public function validated(): array
    {
        $this->validate();

        abort_unless(
            Client::query()->forAuthUser()->whereKey($this->client_id)->exists(),
            422
        );

        $equipment_ids = $this->resolvedEquipmentIds();
        $count = Equipment::query()
            ->forAuthUser()
            ->where('client_id', $this->client_id)
            ->whereIn('id', $equipment_ids)
            ->count();

        abort_unless($count === count($equipment_ids), 422);

        return [
            'quotation_id'        => $this->quotation_id ?: null,
            'diagnosis'           => $this->diagnosis ?: null,
            'estimated_delivery'  => $this->estimated_delivery ?: null,
            'tax_percentage'      => $this->tax_percentage,
            'advance_percentage'  => (float) ($this->advance_percentage ?: 0),
            'advance_amount'      => (float) ($this->advance_amount ?: 0),
            'notes'               => $this->notes ?: null,
            'observations'        => $this->observations ?: null,
            'created_by'          => auth()->id(),
        ];
    }

    /** @return Collection<int, Quotation> */
    public function getAcceptedQuotations(): Collection
    {
        $business_id    = $this->resolvedBusinessId();
        $work_order_id  = $this->work_order_id;

        return Quotation::query()
            ->forAuthUser()
            ->where('business_id', $business_id)
            ->where('status', QuotationStatus::Accepted)
            ->where(function ($query) use ($work_order_id) {
                $query->whereDoesntHave('workOrder');

                if ($work_order_id) {
                    $query->orWhereHas('workOrder', fn ($q) => $q->whereKey($work_order_id));
                }
            })
            ->with(['client:id,name', 'equipments:id,plate,name,brand_name'])
            ->orderByDesc('created_at')
            ->get();
    }
}
