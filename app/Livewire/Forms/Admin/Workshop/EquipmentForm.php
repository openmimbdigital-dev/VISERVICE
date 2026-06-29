<?php

namespace App\Livewire\Forms\Admin\Workshop;

use App\Models\Brand;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\EquipmentModel;
use App\Models\EquipmentType;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class EquipmentForm extends Form
{
    public ?int $equipment_id = null;

    public ?int $business_id = null;

    public ?int $client_id = null;

    public ?int $brand_id = null;

    public ?int $model_id = null;

    public ?int $equipment_type_id = null;

    public string $plate = '';

    public string $year = '';

    public bool $status = true;

    public string $notes = '';

    public function setEquipmentType(EquipmentType $equipment_type): void
    {
        $this->equipment_type_id = $equipment_type->id;
    }

    public function setEquipment(Equipment $equipment): void
    {
        $this->equipment_id      = $equipment->id;
        $this->business_id       = $equipment->business_id;
        $this->client_id         = $equipment->client_id;
        $this->brand_id          = $equipment->brand_id;
        $this->model_id          = $equipment->model_id;
        $this->equipment_type_id = $equipment->equipment_type_id;
        $this->plate             = $equipment->plate;
        $this->year              = $equipment->year ? (string) $equipment->year : '';
        $this->status            = $equipment->status;
        $this->notes             = $equipment->notes ?? '';
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('superAdmin') ?? false;
    }

    public function resolvedBusinessId(): ?int
    {
        if ($this->isSuperAdmin()) {
            return $this->business_id ? (int) $this->business_id : null;
        }

        return auth()->user()?->business_id ? (int) auth()->user()->business_id : null;
    }

    public function rules(): array
    {
        $business_id = $this->resolvedBusinessId();

        $client_rule = Rule::exists('clients', 'id')->where(
            fn ($query) => $query->where('business_id', $business_id)->whereNull('deleted_at')
        );

        $rules = [
            'client_id'         => ['required', 'integer', $client_rule],
            'brand_id'          => ['nullable', 'integer', Rule::in($this->getBrands()->pluck('id')->all())],
            'model_id'          => ['nullable', 'integer', Rule::in($this->getModels()->pluck('id')->all())],
            'equipment_type_id' => ['required', 'integer', 'exists:equipment_types,id'],
            'plate'             => ['required', 'string', 'max:20'],
            'year'              => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'status'            => ['boolean'],
            'notes'             => ['nullable', 'string'],
        ];

        if ($this->isSuperAdmin()) {
            $rules['business_id'] = ['required', 'integer', 'exists:businesses,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'business_id.required'       => 'Debes seleccionar un negocio.',
            'business_id.exists'         => 'El negocio seleccionado no es válido.',
            'client_id.required'         => 'Debes seleccionar un cliente.',
            'client_id.exists'           => 'El cliente seleccionado no es válido para este negocio.',
            'brand_id.in'                => 'La marca seleccionada no es válida.',
            'model_id.in'                => 'El modelo seleccionado no es válido.',
            'equipment_type_id.required' => 'El tipo de equipo es obligatorio.',
            'plate.required'             => 'La placa es obligatoria.',
            'plate.max'                  => 'La placa no puede superar 20 caracteres.',
            'year.integer'               => 'El año debe ser un número válido.',
            'year.min'                   => 'El año no es válido.',
            'year.max'                   => 'El año no puede ser futuro.',
        ];
    }

    public function isEditing(): bool
    {
        return (bool) $this->equipment_id;
    }

    public function getClients(): Collection
    {
        $business_id = $this->resolvedBusinessId();

        if (! $business_id) {
            return collect();
        }

        return Client::query()
            ->where('business_id', $business_id)
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getBrands(): Collection
    {
        $business_id = $this->resolvedBusinessId();

        if (! $business_id) {
            return collect();
        }

        return Brand::query()
            ->active()
            ->where(function ($query) use ($business_id) {
                $query->where('general', true)
                    ->orWhere('business_id', $business_id);
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getModels(): Collection
    {
        if (! $this->brand_id) {
            return collect();
        }

        $business_id = $this->resolvedBusinessId();

        if (! $business_id) {
            return collect();
        }

        return EquipmentModel::query()
            ->active()
            ->where('brand_id', $this->brand_id)
            ->where(function ($query) use ($business_id) {
                $query->where('general', true)
                    ->orWhere('business_id', $business_id);
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function validated(): array
    {
        $this->validate();

        return [
            'client_id'         => (int) $this->client_id,
            'brand_id'          => $this->brand_id ? (int) $this->brand_id : null,
            'model_id'          => $this->model_id ? (int) $this->model_id : null,
            'equipment_type_id' => (int) $this->equipment_type_id,
            'plate'             => strtoupper(trim($this->plate)),
            'year'              => $this->year !== '' ? (int) $this->year : null,
            'status'            => $this->status,
            'notes'             => trim($this->notes) ?: null,
        ];
    }
}
