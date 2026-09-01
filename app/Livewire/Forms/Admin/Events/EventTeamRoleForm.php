<?php

namespace App\Livewire\Forms\Admin\Events;

use App\Models\Business;
use App\Models\EventTeamRole;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class EventTeamRoleForm extends Form
{
    public ?int $event_team_role_id = null;

    public ?int $business_id = null;

    public string $name = '';

    public string $functions = '';

    public bool $active = true;

    public function setEventTeamRole(EventTeamRole $role): void
    {
        $this->event_team_role_id = $role->id;
        $this->business_id = $role->business_id;
        $this->name = $role->name;
        $this->functions = $role->functions ?? '';
        $this->active = (bool) $role->active;
    }

    public function isEditing(): bool
    {
        return $this->event_team_role_id !== null;
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('superAdmin') ?? false;
    }

    public function resolvedBusinessId(): int
    {
        if ($this->isSuperAdmin()) {
            return (int) $this->business_id;
        }

        return (int) auth()->user()->business_id;
    }

    /** @return Collection<int, Business> */
    public function getBusinesses(): Collection
    {
        return Business::query()
            ->whereNull('deleted_at')
            ->whereHas('organization_type', fn ($query) => $query->where('label', 'iglesia'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function rules(): array
    {
        $business_id = $this->isSuperAdmin()
            ? $this->business_id
            : auth()->user()?->business_id;

        $rules = [
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('event_team_roles', 'name')
                    ->where(fn ($query) => $query
                        ->where('business_id', $business_id)
                        ->whereNull('deleted_at'))
                    ->ignore($this->event_team_role_id),
            ],
            'functions' => ['nullable', 'string', 'max:2000'],
            'active' => ['boolean'],
        ];

        if ($this->isSuperAdmin()) {
            $rules['business_id'] = [
                'required',
                'integer',
                Rule::exists('businesses', 'id')->whereNull('deleted_at'),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'business_id.required' => 'Debes seleccionar una iglesia.',
            'business_id.exists' => 'La iglesia seleccionada no es válida.',
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar 150 caracteres.',
            'name.unique' => 'Ya existe un rol de equipo con este nombre.',
            'functions.max' => 'Las funciones no pueden superar 2000 caracteres.',
        ];
    }

    /** @return array{name: string, functions: ?string, active: bool} */
    public function validated(): array
    {
        $data = $this->validate();

        return [
            'name' => trim($data['name']),
            'functions' => filled($data['functions'] ?? null) ? trim($data['functions']) : null,
            'active' => (bool) ($data['active'] ?? true),
        ];
    }
}
