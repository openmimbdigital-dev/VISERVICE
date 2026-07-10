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

    public ?int $equipment_id = null;

    public ?int $quotation_service_type_id = null;

    public ?int $business_payment_method_id = null;

    public ?int $business_bank_account_id = null;

    public string $km_entry = '0';

    public string $hours_entry = '';

    public string $diagnosis = '';

    public string $validity_days = '15';

    public string $execution_time = '';

    public string $tax_percentage = '19';

    public string $notes = '';

    public string $observations = '';

    public function setQuotation(Quotation $quotation): void
    {
        $this->quotation_id              = $quotation->id;
        $this->client_id                 = $quotation->client_id;
        $this->equipment_id              = $quotation->equipment_id;
        $this->quotation_service_type_id = $quotation->quotation_service_type_id;
        $this->business_payment_method_id = $quotation->business_payment_method_id;
        $this->business_bank_account_id   = $quotation->business_bank_account_id;
        $this->km_entry                  = (string) $quotation->km_entry;
        $this->hours_entry               = $quotation->hours_entry !== null ? (string) $quotation->hours_entry : '';
        $this->diagnosis                 = $quotation->diagnosis ?? '';
        $this->validity_days             = (string) ($quotation->validity_days ?? 15);
        $this->execution_time            = $quotation->execution_time ?? '';
        $this->tax_percentage            = (string) $quotation->tax_percentage;
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
            'equipment_id' => [
                'required',
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
            'km_entry'        => ['required', 'integer', 'min:0'],
            'hours_entry'     => ['nullable', 'integer', 'min:0'],
            'diagnosis'       => ['nullable', 'string'],
            'validity_days'   => ['required', 'integer', 'min:1', 'max:365'],
            'execution_time'  => ['nullable', 'string', 'max:120'],
            'tax_percentage'  => ['required', 'numeric', 'min:0', 'max:100'],
            'notes'           => ['nullable', 'string'],
            'observations'    => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required'      => 'Selecciona un cliente.',
            'client_id.exists'        => 'El cliente seleccionado no es válido.',
            'equipment_id.required'   => 'Selecciona un equipo.',
            'equipment_id.exists'     => 'El equipo seleccionado no es válido.',
            'validity_days.required'  => 'Indica los días de vigencia.',
            'validity_days.min'       => 'La vigencia debe ser al menos 1 día.',
            'tax_percentage.required' => 'El porcentaje de IVA es obligatorio.',
        ];
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->quotation_id = null;
        $this->km_entry     = '0';
        $this->hours_entry  = '';
        $this->validity_days = '15';
        $this->tax_percentage = '19';
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

        abort_unless(
            Equipment::query()->forAuthUser()->whereKey($this->equipment_id)->exists(),
            422
        );

        $data = [
            'quotation_service_type_id'  => $this->quotation_service_type_id,
            'business_payment_method_id' => $this->business_payment_method_id,
            'business_bank_account_id'   => $this->business_bank_account_id,
            'km_entry'                   => (int) $this->km_entry,
            'hours_entry'                => $this->hours_entry !== '' ? (int) $this->hours_entry : null,
            'diagnosis'                  => $this->diagnosis ?: null,
            'validity_days'              => (int) $this->validity_days,
            'execution_time'             => $this->execution_time ?: null,
            'tax_percentage'             => $this->tax_percentage,
            'notes'                      => $this->notes ?: null,
            'observations'               => $this->observations ?: null,
        ];

        if (! $this->isEditing()) {
            $data['created_by'] = auth()->id();
        }

        return $data;
    }
}
