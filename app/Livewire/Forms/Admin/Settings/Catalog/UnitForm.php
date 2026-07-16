<?php

namespace App\Livewire\Forms\Admin\Settings\Catalog;

use App\Models\Unit;
use App\Rules\NotConflictingWithGeneralCatalogName;
use App\Support\CatalogLabelNormalizer;
use Illuminate\Validation\Rule;
use Livewire\Form;

class UnitForm extends Form
{
    public ?int $unit_id = null;

    public string $name = '';

    public string $symbol = '';

    public bool $active = true;

    public function setUnit(Unit $unit): void
    {
        $this->unit_id  = $unit->id;
        $this->name     = $unit->name;
        $this->symbol   = $unit->symbol;
        $this->active   = $unit->active;
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->unit_id  = null;
        $this->name     = '';
        $this->symbol   = '';
        $this->active   = true;
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('superAdmin') ?? false;
    }

    public function resolvedBusinessId(): ?int
    {
        return $this->isSuperAdmin() ? null : auth()->user()?->business_id;
    }

    public function resolvedGeneral(): bool
    {
        return $this->isSuperAdmin();
    }

    public function rules(): array
    {
        $business_id = $this->resolvedBusinessId();
        $general     = $this->resolvedGeneral();

        $scope = function ($query) use ($business_id, $general) {
            $query->whereNull('deleted_at');

            if ($general) {
                $query->whereNull('business_id')->where('general', true);
            } else {
                $query->where('business_id', $business_id)->where('general', false);
            }
        };

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                new NotConflictingWithGeneralCatalogName(Unit::class, $this->unit_id),
                Rule::unique('units', 'name')->where($scope)->ignore($this->unit_id),
                function (string $attribute, mixed $value, \Closure $fail) use ($scope) {
                    $name = mb_strtolower(trim((string) $value));

                    if ($name === '') {
                        $fail('El nombre es obligatorio.');

                        return;
                    }

                    $query = Unit::query()->whereRaw('LOWER(TRIM(name)) = ?', [$name]);
                    $scope($query);

                    if ($this->unit_id) {
                        $query->where('id', '!=', $this->unit_id);
                    }

                    if ($query->exists()) {
                        $fail('Ya existe una unidad con este nombre.');

                        return;
                    }

                    $label = CatalogLabelNormalizer::fromName((string) $value);

                    if ($label === '') {
                        $fail('El nombre debe contener al menos una letra o número.');

                        return;
                    }

                    $query = Unit::query()->where('label', $label);
                    $scope($query);

                    if ($this->unit_id) {
                        $query->where('id', '!=', $this->unit_id);
                    }

                    if ($query->exists()) {
                        $fail('Ya existe una unidad con un nombre equivalente.');
                    }
                },
            ],
            'symbol' => ['required', 'string', 'max:20'],
            'active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'El nombre es obligatorio.',
            'name.max'        => 'El nombre no puede superar 100 caracteres.',
            'name.unique'     => 'Ya existe una unidad con este nombre.',
            'symbol.required' => 'El símbolo es obligatorio.',
            'symbol.max'      => 'El símbolo no puede superar 20 caracteres.',
        ];
    }

    public function isEditing(): bool
    {
        return (bool) $this->unit_id;
    }

    public function validated(): array
    {
        $this->validate();

        return [
            'name'   => trim($this->name),
            'symbol' => trim($this->symbol),
            'active' => $this->active,
        ];
    }
}
