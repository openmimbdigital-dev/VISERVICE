<?php

namespace App\Livewire\Forms\Admin\Events;

use App\Models\Business;
use App\Models\EventTeam;
use App\Models\EventTeamRole;
use App\Models\Participant;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class EventTeamForm extends Form
{
    public ?int $event_team_id = null;

    public ?int $business_id = null;

    public string $name = '';

    public string $description = '';

    public bool $active = true;

    /** @var list<int|string> */
    public array $role_ids = [];

    /** @var list<array{participant_id: string|int|null, event_team_role_id: string|int|null}> */
    public array $members = [];

    public function setEventTeam(EventTeam $event_team): void
    {
        $event_team->loadMissing(['roles:id', 'members']);

        $this->event_team_id = $event_team->id;
        $this->business_id = $event_team->business_id;
        $this->name = $event_team->name;
        $this->description = $event_team->description ?? '';
        $this->active = (bool) $event_team->active;
        $this->role_ids = $event_team->roles->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->members = $event_team->members
            ->map(fn ($member) => [
                'participant_id' => $member->participant_id,
                'event_team_role_id' => $member->event_team_role_id,
            ])
            ->values()
            ->all();
    }

    public function isEditing(): bool
    {
        return $this->event_team_id !== null;
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

    public function addMember(): void
    {
        $this->members[] = [
            'participant_id' => null,
            'event_team_role_id' => null,
        ];
    }

    public function removeMember(int $index): void
    {
        unset($this->members[$index]);
        $this->members = array_values($this->members);
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

    /** @return Collection<int, EventTeamRole> */
    public function getRoles(): Collection
    {
        $business_id = $this->resolvedBusinessId();

        if (! $business_id) {
            return collect();
        }

        return EventTeamRole::query()
            ->where('business_id', $business_id)
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'functions']);
    }

    /** @return Collection<int, Participant> */
    public function getParticipants(): Collection
    {
        $business_id = $this->resolvedBusinessId();

        if (! $business_id) {
            return collect();
        }

        return Participant::query()
            ->where('business_id', $business_id)
            ->whereNull('deleted_at')
            ->where('status', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);
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
                Rule::unique('event_teams', 'name')
                    ->where(fn ($query) => $query
                        ->where('business_id', $business_id)
                        ->whereNull('deleted_at'))
                    ->ignore($this->event_team_id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'active' => ['boolean'],
            'role_ids' => ['array'],
            'role_ids.*' => [
                'integer',
                Rule::exists('event_team_roles', 'id')
                    ->where(fn ($query) => $query
                        ->where('business_id', $business_id)
                        ->whereNull('deleted_at')),
            ],
            'members' => ['array'],
            'members.*.participant_id' => [
                'required',
                'integer',
                Rule::exists('participants', 'id')
                    ->where(fn ($query) => $query
                        ->where('business_id', $business_id)
                        ->whereNull('deleted_at')),
            ],
            'members.*.event_team_role_id' => [
                'required',
                'integer',
                Rule::in(array_map('intval', $this->role_ids)),
            ],
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
            'name.unique' => 'Ya existe un equipo de evento con este nombre.',
            'description.max' => 'La descripción no puede superar 2000 caracteres.',
            'role_ids.*.exists' => 'Uno de los roles seleccionados no es válido.',
            'members.*.participant_id.required' => 'Selecciona un participante para cada integrante.',
            'members.*.participant_id.exists' => 'Uno de los participantes seleccionados no es válido.',
            'members.*.event_team_role_id.required' => 'Selecciona un rol para cada integrante.',
            'members.*.event_team_role_id.in' => 'El rol del integrante debe pertenecer al equipo.',
        ];
    }

    /**
     * @return array{
     *     name: string,
     *     description: ?string,
     *     active: bool,
     *     role_ids: list<int>,
     *     members: list<array{participant_id: int, event_team_role_id: int}>
     * }
     */
    public function validated(): array
    {
        $data = $this->validate();

        $members = collect($data['members'] ?? [])
            ->map(fn (array $member) => [
                'participant_id' => (int) $member['participant_id'],
                'event_team_role_id' => (int) $member['event_team_role_id'],
            ])
            ->unique(fn (array $member) => $member['participant_id'].'-'.$member['event_team_role_id'])
            ->values()
            ->all();

        return [
            'name' => trim($data['name']),
            'description' => filled($data['description'] ?? null) ? trim($data['description']) : null,
            'active' => (bool) ($data['active'] ?? true),
            'role_ids' => array_values(array_unique(array_map('intval', $data['role_ids'] ?? []))),
            'members' => $members,
        ];
    }
}
