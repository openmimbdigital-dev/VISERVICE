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
    public const STEP_GENERAL = 1;

    public const STEP_CONDITIONS = 2;

    public const STEP_ITEMS = 3;

    public const TOTAL_STEPS = 3;

    public ?int $work_order_id = null;

    public ?int $quotation_id = null;

    public ?int $client_id = null;

    public ?int $coupon_id = null;

    /** Código del cupón aplicado, para mostrarlo sin volver a consultarlo. */
    public string $coupon_code = '';

    /** @var list<int|string> */
    public array $equipment_ids = [];

    public string $diagnosis = '';

    public string $estimated_delivery = '';

    public string $tax_percentage = '0';

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
        $this->coupon_id           = $work_order->coupon_id;
        $this->coupon_code         = $work_order->coupon_code ?? '';
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
        $this->tax_percentage = (string) ($quotation->tax_percentage ?? 0);
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
        return array_merge(
            $this->rulesForStep(self::STEP_GENERAL),
            $this->rulesForStep(self::STEP_CONDITIONS),
        );
    }

    /** @return array<string, mixed> */
    public function rulesForStep(int $step): array
    {
        $this->normalizeOptionalFields();

        $business_id = $this->resolvedBusinessId();

        return match ($step) {
            self::STEP_GENERAL => [
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
            ],
            self::STEP_CONDITIONS => [
                'diagnosis'          => ['nullable', 'string'],
                'estimated_delivery' => ['nullable', 'date'],
                'tax_percentage'     => ['nullable', 'numeric', 'min:0', 'max:100'],
                'advance_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'notes'              => ['nullable', 'string'],
                'observations'       => ['nullable', 'string'],
            ],
            default => [],
        };
    }

    public function firstStepWithErrors(array $errors): int
    {
        $step_fields = [
            self::STEP_GENERAL => ['quotation_id', 'client_id', 'equipment_ids'],
            self::STEP_CONDITIONS => [
                'diagnosis', 'estimated_delivery', 'tax_percentage',
                'advance_percentage', 'notes', 'observations',
            ],
            self::STEP_ITEMS => ['items', 'coupon_code', 'coupon_id'],
        ];

        $error_keys = collect(array_keys($errors))
            ->map(fn (string $key) => str_replace('form.', '', $key))
            ->all();

        foreach ($step_fields as $step => $fields) {
            foreach ($error_keys as $key) {
                foreach ($fields as $field) {
                    if ($key === $field || str_starts_with($key, $field . '.')) {
                        return $step;
                    }
                }
            }
        }

        return self::STEP_GENERAL;
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
            'tax_percentage.numeric'      => 'El porcentaje de IVA debe ser un número.',
            'advance_percentage.numeric'  => 'El anticipo debe ser un número.',
            'advance_percentage.min'      => 'El anticipo no puede ser negativo.',
            'advance_percentage.max'      => 'El anticipo no puede superar el 100%.',
            'estimated_delivery.date'     => 'La fecha de entrega estimada no es válida.',
        ];
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->work_order_id = null;
        $this->quotation_id = null;
        $this->coupon_id = null;
        $this->coupon_code = '';
        $this->equipment_ids = [];
        $this->tax_percentage = '0';
        $this->advance_percentage = '0';
        $this->advance_amount = '0';
    }

    /** @return array<string, mixed> */
    public function validated(): array
    {
        $this->normalizeOptionalFields();

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

        $data = $this->payload(self::TOTAL_STEPS);

        if (! $this->isEditing()) {
            $data['created_by'] = auth()->id();
        }

        return $data;
    }

    /** @return array<string, mixed> */
    public function payload(int $step): array
    {
        $this->normalizeOptionalFields();

        $data = [
            'quotation_id'        => $this->quotation_id ?: null,
            'coupon_id'           => $this->coupon_id ?: null,
            'diagnosis'           => $this->diagnosis ?: null,
            'estimated_delivery'  => $this->estimated_delivery ?: null,
            'tax_percentage'      => $this->tax_percentage !== '' ? $this->tax_percentage : 0,
            'advance_percentage'  => (float) ($this->advance_percentage ?: 0),
            'advance_amount'      => (float) ($this->advance_amount ?: 0),
            'notes'               => $this->notes ?: null,
            'observations'        => $this->observations ?: null,
            'step'                => $step,
            'final_step'          => self::TOTAL_STEPS,
        ];

        if (! $this->isEditing()) {
            $data['created_by'] = auth()->id();
        }

        return $data;
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

    private function normalizeOptionalFields(): void
    {
        if ($this->quotation_id === 0) {
            $this->quotation_id = null;
        }

        if ($this->tax_percentage === '') {
            $this->tax_percentage = '0';
        }

        if ($this->advance_percentage === '') {
            $this->advance_percentage = '0';
        }
    }
}
