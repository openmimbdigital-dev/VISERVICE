<?php

namespace App\Livewire\Forms\Admin\Workshop;

use App\Models\Client;
use App\Models\Equipment;
use App\Models\Quotation;
use App\Models\QuotationServiceType;
use Illuminate\Validation\Rule;
use Livewire\Form;

class QuotationForm extends Form
{
    public ?int $quotation_id = null;

    public ?int $client_id = null;

    /** @var list<int|string> */
    public array $equipment_ids = [];

    public ?int $quotation_service_type_id = null;

    public ?int $business_payment_method_id = null;

    public ?int $business_bank_account_id = null;

    public string $hours_entry = '';

    public string $diagnosis = '';

    public string $validity_days = '15';

    public string $execution_time = '';

    public string $tax_percentage = '19';

    public string $advance_percentage = '0';

    public string $notes = '';

    public string $observations = '';

    public function setQuotation(Quotation $quotation): void
    {
        $quotation->loadMissing('equipments:id');

        $this->quotation_id              = $quotation->id;
        $this->client_id                 = $quotation->client_id;
        $this->equipment_ids             = $quotation->equipments->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $this->quotation_service_type_id = $quotation->quotation_service_type_id;
        $this->business_payment_method_id = $quotation->business_payment_method_id;
        $this->business_bank_account_id   = $quotation->business_bank_account_id;
        $this->hours_entry               = $quotation->hours_entry_formatted ?? '';
        $this->diagnosis                 = $quotation->diagnosis ?? '';
        $this->validity_days             = (string) ($quotation->validity_days ?? 15);
        $this->execution_time            = $quotation->execution_time ?? '';
        $this->tax_percentage            = (string) $quotation->tax_percentage;
        $this->advance_percentage        = (string) ($quotation->advance_percentage ?? 0);
        $this->notes                     = $quotation->notes ?? '';
        $this->observations              = $quotation->observations ?? '';
    }

    public function isEditing(): bool
    {
        return (bool) $this->quotation_id;
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
            'quotation_service_type_id' => [
                'nullable',
                'integer',
                Rule::exists('quotation_service_types', 'id')->whereNull('deleted_at'),
            ],
            'business_payment_method_id' => [
                'nullable',
                'integer',
                Rule::exists('business_payment_methods', 'id')->whereNull('deleted_at'),
            ],
            'business_bank_account_id' => [
                'nullable',
                'integer',
                Rule::exists('business_bank_accounts', 'id')->where(fn ($q) => $q
                    ->where('business_id', $business_id)
                    ->whereNull('deleted_at')),
            ],
            'hours_entry'     => ['nullable', 'date_format:H:i'],
            'diagnosis'       => ['nullable', 'string'],
            'validity_days'   => ['required', 'integer', 'min:1', 'max:365'],
            'execution_time'  => ['nullable', 'string', 'max:120'],
            'tax_percentage'  => ['required', 'numeric', 'min:0', 'max:100'],
            'advance_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'notes'           => ['nullable', 'string'],
            'observations'    => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required'      => 'Selecciona un cliente.',
            'client_id.exists'        => 'El cliente seleccionado no es válido.',
            'equipment_ids.required'  => 'Selecciona al menos un equipo.',
            'equipment_ids.min'       => 'Selecciona al menos un equipo.',
            'equipment_ids.*.exists'  => 'Uno o más equipos no son válidos para el cliente.',
            'hours_entry.date_format' => 'Las horas al ingreso deben tener formato HH:MM.',
            'validity_days.required'  => 'Indica los días de vigencia.',
            'validity_days.min'       => 'La vigencia debe ser al menos 1 día.',
            'tax_percentage.required' => 'El porcentaje de IVA es obligatorio.',
            'advance_percentage.required' => 'Indica el porcentaje de anticipo.',
            'advance_percentage.numeric' => 'El anticipo debe ser un número.',
            'advance_percentage.min' => 'El anticipo no puede ser negativo.',
            'advance_percentage.max' => 'El anticipo no puede superar el 100%.',
        ];
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->quotation_id = null;
        $this->equipment_ids = [];
        $this->hours_entry  = '';
        $this->validity_days = '15';
        $this->tax_percentage = '19';
        $this->advance_percentage = '0';
    }

    public function validated(): array
    {
        foreach (['quotation_service_type_id', 'business_payment_method_id', 'business_bank_account_id'] as $field) {
            if ($this->{$field} === '' || $this->{$field} === 0) {
                $this->{$field} = null;
            }
        }

        $this->validate();

        if ($this->quotation_service_type_id) {
            abort_unless(
                QuotationServiceType::query()->visibleToUser()->whereKey($this->quotation_service_type_id)->exists(),
                422
            );
        }

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

        $data = [
            'quotation_service_type_id'  => $this->quotation_service_type_id,
            'business_payment_method_id' => $this->business_payment_method_id,
            'business_bank_account_id'   => $this->business_bank_account_id,
            'hours_entry'                => $this->hours_entry !== '' ? $this->hours_entry : null,
            'diagnosis'                  => $this->diagnosis ?: null,
            'validity_days'              => (int) $this->validity_days,
            'execution_time'             => $this->execution_time ?: null,
            'tax_percentage'             => $this->tax_percentage,
            'advance_percentage'         => $this->advance_percentage,
            'notes'                      => $this->notes ?: null,
            'observations'               => $this->observations ?: null,
        ];

        if (! $this->isEditing()) {
            $data['created_by'] = auth()->id();
        }

        return $data;
    }
}
