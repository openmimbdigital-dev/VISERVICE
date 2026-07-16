<?php

namespace App\Livewire\Forms\Admin;

use App\Models\OrganizationType;
use App\Models\TeamPosition;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class TeamPositionForm extends Form
{
    public ?int $team_position_id = null;

    public string $name = '';

    public bool $active = true;

    public ?int $organization_type_id = null;

    public function setTeamPosition(TeamPosition $team_position): void
    {
        $this->team_position_id     = $team_position->id;
        $this->name                 = $team_position->name;
        $this->active               = $team_position->active;
        $this->organization_type_id = $team_position->organization_type_id;
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->team_position_id     = null;
        $this->name                 = '';
        $this->active               = true;
        $this->organization_type_id = null;
    }

    public function isEditing(): bool
    {
        return $this->team_position_id !== null;
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('superAdmin') ?? false;
    }

    public function resolvedBusinessId(): ?int
    {
        return $this->isSuperAdmin() ? null : auth()->user()?->business_id;
    }

    public function getOrganizationTypes(): Collection
    {
        return OrganizationType::query()
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function rules(): array
    {
        $business_id = $this->resolvedBusinessId();
        $general     = $this->isSuperAdmin();

        $label_scope = function ($query) use ($business_id, $general) {
            $query->whereNull('deleted_at');

            if ($general) {
                $query->where('general', true)->whereNull('business_id');
            } else {
                $query->where('general', false)->where('business_id', $business_id);
            }
        };

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('team_positions', 'name')
                    ->where($label_scope)
                    ->ignore($this->team_position_id),
            ],
            'active'               => ['boolean'],
            'organization_type_id' => [
                Rule::requiredIf($general),
                'nullable',
                'integer',
                Rule::exists('organization_types', 'id')->where(fn ($q) => $q->where('status', true)),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                 => 'El nombre es obligatorio.',
            'name.unique'                   => 'Ya existe un cargo con ese nombre.',
            'organization_type_id.required' => 'Selecciona el tipo de organización.',
            'organization_type_id.exists'   => 'El tipo de organización no es válido.',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $this->validate();

        return [
            'name'                 => trim($this->name),
            'active'               => $this->active,
            'organization_type_id' => $this->organization_type_id,
        ];
    }
}
