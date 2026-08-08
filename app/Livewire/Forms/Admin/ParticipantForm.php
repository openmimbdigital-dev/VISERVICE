<?php

namespace App\Livewire\Forms\Admin;

use App\Enums\DocumentType;
use App\Models\Business;
use App\Models\City;
use App\Models\Country;
use App\Models\Participant;
use App\Models\ParticipantRole;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ParticipantForm extends Form
{
    public ?int $participant_id = null;

    public ?int $business_id = null;

    public ?int $participant_role_id = null;

    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $phone_number = '';

    public string $address = '';

    public bool $status = true;

    public string $document_type = '';

    public string $document_number = '';

    public ?int $city_id = null;

    public ?int $country_id = null;

    public function setParticipant(Participant $participant): void
    {
        $this->participant_id = $participant->id;
        $this->business_id = $participant->business_id;
        $this->participant_role_id = $participant->participant_role_id;
        $this->first_name = $participant->first_name ?? '';
        $this->last_name = $participant->last_name ?? '';
        $this->email = $participant->email ?? '';
        $this->phone_number = $participant->phone_number ?? '';
        $this->address = $participant->address ?? '';
        $this->status = (bool) $participant->status;
        $this->document_type = $participant->document_type?->value ?? '';
        $this->document_number = $participant->document_number !== null
            ? (string) $participant->document_number
            : '';
        $this->city_id = $participant->city_id;
        $this->country_id = $participant->country_id;
    }

    public function isEditing(): bool
    {
        return $this->participant_id !== null;
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

    /** @return Collection<int, ParticipantRole> */
    public function getRoles(): Collection
    {
        $business_id = $this->resolvedBusinessId();

        if (! $business_id) {
            return collect();
        }

        return ParticipantRole::query()
            ->forAuthUser()
            ->where('business_id', $business_id)
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /** @return Collection<int, City> */
    public function getCities(): Collection
    {
        return City::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'state_province']);
    }

    /** @return Collection<int, Country> */
    public function getCountries(): Collection
    {
        return Country::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function rules(): array
    {
        $business_id = $this->isSuperAdmin()
            ? $this->business_id
            : auth()->user()?->business_id;

        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:200'],
            'status' => ['boolean'],
            'document_type' => ['nullable', Rule::in(array_column(DocumentType::cases(), 'value'))],
            'document_number' => [
                'nullable',
                'integer',
                Rule::unique('participants', 'document_number')
                    ->where(fn ($query) => $query
                        ->where('business_id', $business_id)
                        ->whereNull('deleted_at'))
                    ->ignore($this->participant_id),
            ],
            'participant_role_id' => [
                'nullable',
                'integer',
                Rule::exists('participant_roles', 'id')
                    ->where(fn ($query) => $query
                        ->where('business_id', $business_id)
                        ->whereNull('deleted_at')),
            ],
            'city_id' => ['nullable', 'integer', Rule::exists('cities', 'id')->whereNull('deleted_at')],
            'country_id' => ['nullable', 'integer', Rule::exists('countries', 'id')->whereNull('deleted_at')],
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
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'El apellido es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
            'document_type.in' => 'El tipo de documento no es válido.',
            'document_number.integer' => 'El número de documento debe ser numérico.',
            'document_number.unique' => 'Ya existe un participante con este documento.',
            'participant_role_id.exists' => 'El rol seleccionado no es válido.',
            'city_id.exists' => 'La ciudad seleccionada no es válida.',
            'country_id.exists' => 'El país seleccionado no es válido.',
        ];
    }

    /**
     * @return array{
     *     participant_role_id: ?int,
     *     first_name: string,
     *     last_name: string,
     *     email: ?string,
     *     phone_number: ?string,
     *     address: ?string,
     *     status: bool,
     *     document_type: ?string,
     *     document_number: ?int,
     *     city_id: ?int,
     *     country_id: ?int
     * }
     */
    public function validated(): array
    {
        $data = $this->validate();

        return [
            'participant_role_id' => filled($data['participant_role_id'] ?? null)
                ? (int) $data['participant_role_id']
                : null,
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'email' => filled($data['email'] ?? null) ? trim($data['email']) : null,
            'phone_number' => filled($data['phone_number'] ?? null) ? trim($data['phone_number']) : null,
            'address' => filled($data['address'] ?? null) ? trim($data['address']) : null,
            'status' => (bool) ($data['status'] ?? true),
            'document_type' => filled($data['document_type'] ?? null) ? $data['document_type'] : null,
            'document_number' => filled($data['document_number'] ?? null) ? (int) $data['document_number'] : null,
            'city_id' => filled($data['city_id'] ?? null) ? (int) $data['city_id'] : null,
            'country_id' => filled($data['country_id'] ?? null) ? (int) $data['country_id'] : null,
        ];
    }
}
