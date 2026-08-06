<?php

namespace App\Livewire\Forms\Admin\Workshop;

use App\Enums\QuotationStatus;
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

    public ?int $equipment_id = null;

    public string $km_entry = '0';

    public string $diagnosis = '';

    public string $estimated_delivery = '';

    public string $tax_percentage = '19';

    public string $notes = '';

    public string $observations = '';

    public function setWorkOrder(WorkOrder $work_order): void
    {
        $this->work_order_id       = $work_order->id;
        $this->quotation_id        = $work_order->quotation_id;
        $this->client_id           = $work_order->client_id;
        $this->equipment_id        = $work_order->equipment_id;
        $this->km_entry            = (string) ($work_order->km_entry ?? 0);
        $this->diagnosis           = $work_order->diagnosis ?? '';
        $this->estimated_delivery  = $work_order->estimated_delivery?->format('Y-m-d') ?? '';
        $this->tax_percentage      = (string) $work_order->tax_percentage;
        $this->notes               = $work_order->notes ?? '';
        $this->observations        = $work_order->observations ?? '';
    }

    public function applyQuotation(Quotation $quotation): void
    {
        $this->quotation_id = $quotation->id;
        $this->client_id    = $quotation->client_id;
        $this->equipment_id = $quotation->equipment_id;
        $this->diagnosis    = $quotation->diagnosis ?? '';
        $this->tax_percentage = (string) ($quotation->tax_percentage ?? 19);
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
            'equipment_id' => [
                'required',
                'integer',
                Rule::exists('equipment', 'id')->where(fn ($q) => $q
                    ->where('business_id', $business_id)
                    ->where('client_id', $this->client_id)
                    ->whereNull('deleted_at')),
            ],
            'km_entry'           => ['required', 'integer', 'min:0'],
            'diagnosis'          => ['nullable', 'string'],
            'estimated_delivery' => ['nullable', 'date'],
            'tax_percentage'     => ['required', 'numeric', 'min:0', 'max:100'],
            'notes'              => ['nullable', 'string'],
            'observations'       => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required'          => 'Selecciona un cliente.',
            'client_id.exists'            => 'El cliente seleccionado no es válido.',
            'equipment_id.required'       => 'Selecciona un equipo.',
            'equipment_id.exists'         => 'El equipo seleccionado no es válido.',
            'quotation_id.exists'         => 'La cotización debe existir, pertenecer al negocio y estar aceptada.',
            'km_entry.required'           => 'Indica el kilometraje de ingreso.',
            'km_entry.integer'            => 'El kilometraje debe ser un número entero.',
            'tax_percentage.required'     => 'Indica el porcentaje de IVA.',
            'estimated_delivery.date'     => 'La fecha de entrega estimada no es válida.',
        ];
    }

    /** @return array<string, mixed> */
    public function validated(): array
    {
        $data = $this->validate();

        return [
            'quotation_id'        => $data['quotation_id'] ?: null,
            'km_entry'            => (int) $data['km_entry'],
            'diagnosis'           => $data['diagnosis'] ?: null,
            'estimated_delivery'  => $data['estimated_delivery'] ?: null,
            'tax_percentage'      => $data['tax_percentage'],
            'notes'               => $data['notes'] ?: null,
            'observations'        => $data['observations'] ?: null,
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
            ->with(['client:id,name', 'equipment:id,plate'])
            ->orderByDesc('created_at')
            ->get();
    }
}
