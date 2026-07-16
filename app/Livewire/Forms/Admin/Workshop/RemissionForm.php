<?php

namespace App\Livewire\Forms\Admin\Workshop;

use App\Models\Remission;
use App\Models\WorkOrder;
use App\Support\CurrentBusiness;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class RemissionForm extends Form
{
    public ?int $remission_id = null;

    public ?int $work_order_id = null;

    public string $type = 'entrega';

    public string $status = 'borrador';

    public string $quotation_or_po_reference = '';

    public string $issue_date = '';

    public string $delivery_address = '';

    public string $delivery_city = '';

    public string $delivery_contact = '';

    public string $delivery_phone = '';

    public string $delivery_observations = '';

    public string $observations = '';

    public string $delivered_by_name = '';

    public string $delivered_by_position = '';

    public string $delivered_by_document = '';

    public string $received_by_name = '';

    public string $received_by_position = '';

    public string $received_by_document = '';

    public function setRemission(Remission $remission): void
    {
        $this->remission_id             = $remission->id;
        $this->work_order_id            = $remission->work_order_id;
        $this->type                     = $remission->type ?? 'entrega';
        $this->status                   = $remission->status ?? 'borrador';
        $this->quotation_or_po_reference = $remission->quotation_or_po_reference ?? '';
        $this->issue_date               = $remission->issue_date?->format('Y-m-d') ?? '';
        $this->delivery_address         = $remission->delivery_address ?? '';
        $this->delivery_city            = $remission->delivery_city ?? '';
        $this->delivery_contact         = $remission->delivery_contact ?? '';
        $this->delivery_phone           = $remission->delivery_phone ?? '';
        $this->delivery_observations    = $remission->delivery_observations ?? '';
        $this->observations             = $remission->observations ?? '';
        $this->delivered_by_name        = $remission->delivered_by_name ?? '';
        $this->delivered_by_position    = $remission->delivered_by_position ?? '';
        $this->delivered_by_document    = $remission->delivered_by_document ?? '';
        $this->received_by_name         = $remission->received_by_name ?? '';
        $this->received_by_position     = $remission->received_by_position ?? '';
        $this->received_by_document     = $remission->received_by_document ?? '';
    }

    public function isEditing(): bool
    {
        return (bool) $this->remission_id;
    }

    public function resolvedBusinessId(): int
    {
        return (int) (CurrentBusiness::id() ?? auth()->user()?->business_id);
    }

    public function rules(): array
    {
        $business_id = $this->resolvedBusinessId();

        return [
            'work_order_id' => [
                'required',
                'integer',
                Rule::exists('work_orders', 'id')->where(fn ($q) => $q
                    ->where('business_id', $business_id)
                    ->whereIn('status', ['abierta', 'en_proceso'])
                    ->whereNull('deleted_at')),
                Rule::unique('remissions', 'work_order_id')
                    ->whereNull('deleted_at')
                    ->ignore($this->remission_id),
            ],
            'type' => ['required', Rule::in(['entrega', 'devolucion', 'traslado'])],
            'status' => ['required', Rule::in(['borrador', 'emitida', 'entregada'])],
            'quotation_or_po_reference' => ['nullable', 'string', 'max:150'],
            'issue_date' => ['nullable', 'date'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
            'delivery_city' => ['nullable', 'string', 'max:150'],
            'delivery_contact' => ['nullable', 'string', 'max:150'],
            'delivery_phone' => ['nullable', 'string', 'max:50'],
            'delivery_observations' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'delivered_by_name' => ['required', 'string', 'max:150'],
            'delivered_by_position' => ['required', 'string', 'max:100'],
            'delivered_by_document' => ['required', 'string', 'max:50'],
            'received_by_name' => ['required', 'string', 'max:150'],
            'received_by_position' => ['required', 'string', 'max:100'],
            'received_by_document' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'work_order_id.required' => 'Selecciona una orden de trabajo.',
            'work_order_id.exists'   => 'La OT debe existir, pertenecer al negocio y estar abierta o en proceso.',
            'work_order_id.unique'   => 'Esta OT ya tiene una remisión generada.',
            'type.required'          => 'Selecciona el tipo de remisión.',
            'type.in'                => 'El tipo de remisión no es válido.',
            'status.required'        => 'Selecciona el estado.',
            'status.in'              => 'El estado no es válido.',
            'issue_date.date'        => 'La fecha de expedición no es válida.',
            'delivered_by_name.required'     => 'El nombre de quien entrega es obligatorio.',
            'delivered_by_position.required' => 'El cargo de quien entrega es obligatorio.',
            'delivered_by_document.required' => 'La C.C. de quien entrega es obligatoria.',
            'received_by_name.required'      => 'El nombre de quien recibe es obligatorio.',
            'received_by_position.required'  => 'El cargo de quien recibe es obligatorio.',
            'received_by_document.required'  => 'La C.C. de quien recibe es obligatoria.',
        ];
    }

    /** @return array<string, mixed> */
    public function validated(): array
    {
        $data = $this->validate();

        return [
            'work_order_id'             => (int) $data['work_order_id'],
            'type'                      => $data['type'],
            'status'                    => $data['status'],
            'quotation_or_po_reference' => $data['quotation_or_po_reference'] ?: null,
            'issue_date'                => $data['issue_date'] ?: null,
            'delivery_address'          => $data['delivery_address'] ?: null,
            'delivery_city'             => $data['delivery_city'] ?: null,
            'delivery_contact'          => $data['delivery_contact'] ?: null,
            'delivery_phone'            => $data['delivery_phone'] ?: null,
            'delivery_observations'     => $data['delivery_observations'] ?: null,
            'observations'              => $data['observations'] ?: null,
            'delivered_by_name'         => trim($data['delivered_by_name']),
            'delivered_by_position'     => trim($data['delivered_by_position']),
            'delivered_by_document'     => trim($data['delivered_by_document']),
            'received_by_name'          => trim($data['received_by_name']),
            'received_by_position'      => trim($data['received_by_position']),
            'received_by_document'      => trim($data['received_by_document']),
            'created_by'                => auth()->id(),
        ];
    }

    /** @return Collection<int, WorkOrder> */
    public function getEligibleWorkOrders(): Collection
    {
        $business_id = $this->resolvedBusinessId();

        return WorkOrder::query()
            ->forAuthUser()
            ->where('business_id', $business_id)
            ->where(function ($query) {
                $query->whereIn('status', ['abierta', 'en_proceso']);

                if ($this->work_order_id) {
                    $query->orWhere('work_orders.id', $this->work_order_id);
                }
            })
            ->where(function ($query) {
                $query->whereDoesntHave('remissions');

                if ($this->remission_id) {
                    $query->orWhereHas('remissions', fn ($q) => $q->whereKey($this->remission_id));
                }
            })
            ->with(['client:id,name', 'equipment:id,plate'])
            ->orderByDesc('created_at')
            ->get();
    }
}
