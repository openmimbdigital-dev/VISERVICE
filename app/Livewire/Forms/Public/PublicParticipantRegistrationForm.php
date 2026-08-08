<?php

namespace App\Livewire\Forms\Public;

use App\Enums\DocumentType;
use App\Models\City;
use App\Models\Country;
use App\Models\ParticipantRole;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class PublicParticipantRegistrationForm extends Form
{
    public int $business_id;

    public ?int $participant_role_id = null;

    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $phone_number = '';

    public string $address = '';

    public string $document_type = '';

    public string $document_number = '';

    public ?int $city_id = null;

    public ?int $country_id = null;

    public function setBusinessId(int $business_id): void
    {
        $this->business_id = $business_id;
    }

    public function clearInputs(): void
    {
        $this->participant_role_id = null;
        $this->first_name = '';
        $this->last_name = '';
        $this->email = '';
        $this->phone_number = '';
        $this->address = '';
        $this->document_type = '';
        $this->document_number = '';
        $this->city_id = null;
        $this->country_id = null;
    }

    /** @return Collection<int, ParticipantRole> */
    public function getRoles(): Collection
    {
        return ParticipantRole::query()
            ->where('business_id', $this->business_id)
            ->where('active', true)
            ->whereNull('deleted_at')
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
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:200'],
            'document_type' => ['nullable', Rule::in(array_column(DocumentType::cases(), 'value'))],
            'document_number' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('participants', 'document_number')
                    ->where(fn ($query) => $query
                        ->where('business_id', $this->business_id)
                        ->whereNull('deleted_at')),
            ],
            'participant_role_id' => [
                'nullable',
                'integer',
                Rule::exists('participant_roles', 'id')
                    ->where(fn ($query) => $query
                        ->where('business_id', $this->business_id)
                        ->whereNull('deleted_at')),
            ],
            'city_id' => ['nullable', 'integer', Rule::exists('cities', 'id')->whereNull('deleted_at')],
            'country_id' => ['nullable', 'integer', Rule::exists('countries', 'id')->whereNull('deleted_at')],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'El apellido es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
            'document_type.in' => 'El tipo de documento no es válido.',
            'document_number.max' => 'El número de documento no puede superar 30 caracteres.',
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
     *     document_type: ?string,
     *     document_number: ?string,
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
            'document_type' => filled($data['document_type'] ?? null) ? $data['document_type'] : null,
            'document_number' => filled($data['document_number'] ?? null) ? trim((string) $data['document_number']) : null,
            'city_id' => filled($data['city_id'] ?? null) ? (int) $data['city_id'] : null,
            'country_id' => filled($data['country_id'] ?? null) ? (int) $data['country_id'] : null,
        ];
    }
}
