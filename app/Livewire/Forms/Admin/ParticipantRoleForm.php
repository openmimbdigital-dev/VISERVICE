<?php

namespace App\Livewire\Forms\Admin;

use App\Models\Business;
use App\Models\ParticipantRole;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ParticipantRoleForm extends Form
{
    public ?int $participant_role_id = null;

    public ?int $business_id = null;

    public string $name = '';

    public string $description = '';

    public bool $active = true;

    public function setParticipantRole(ParticipantRole $role): void
    {
        $this->participant_role_id = $role->id;
        $this->business_id = $role->business_id;
        $this->name = $role->name;
        $this->description = $role->description ?? '';
        $this->active = (bool) $role->active;
    }

    public function isEditing(): bool
    {
        return $this->participant_role_id !== null;
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
            ->where('status', true)
            ->whereNull('deleted_at')
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
                Rule::unique('participant_roles', 'name')
                    ->where(fn ($query) => $query
                        ->where('business_id', $business_id)
                        ->whereNull('deleted_at'))
                    ->ignore($this->participant_role_id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
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
            'business_id.required' => 'Debes seleccionar un negocio.',
            'business_id.exists' => 'El negocio seleccionado no es válido.',
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar 150 caracteres.',
            'name.unique' => 'Ya existe un rol de participante con este nombre.',
            'description.max' => 'La descripción no puede superar 2000 caracteres.',
        ];
    }

    /** @return array{name: string, description: ?string, active: bool} */
    public function validated(): array
    {
        $data = $this->validate();

        return [
            'name' => trim($data['name']),
            'description' => filled($data['description'] ?? null) ? trim($data['description']) : null,
            'active' => (bool) ($data['active'] ?? true),
        ];
    }
}
