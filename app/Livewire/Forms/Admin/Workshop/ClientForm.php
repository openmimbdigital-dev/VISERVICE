<?php

namespace App\Livewire\Forms\Admin\Workshop;

use App\Models\Client;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ClientForm extends Form
{
    public ?int $client_id = null;
    public ?int $business_id = null;
    public ?int $city_id = null;

    public string $name            = '';
    public string $document_type   = 'CC';
    public string $document_number = '';
    public string $verification_digit = '';
    public int    $person_type     = 2;
    public string $fiscal_responsibilities = 'R-99-PN';
    public string $phone           = '';
    public string $email           = '';
    public string $address         = '';
    public string $contact_name    = '';
    public bool   $status          = true;
    public string $notes           = '';

    public function setClient(Client $client): void
    {
        $this->client_id       = $client->id;
        $this->business_id     = $client->business_id;
        $this->city_id         = $client->city_id;
        $this->name            = $client->name;
        $this->document_type   = $client->document_type;
        $this->document_number = $client->document_number ?? '';
        $this->verification_digit = $client->verification_digit ?? '';
        $this->person_type     = $client->person_type ?? 2;
        $this->fiscal_responsibilities = $client->fiscal_responsibilities ?? 'R-99-PN';
        $this->phone           = $client->phone ?? '';
        $this->email           = $client->email ?? '';
        $this->address         = $client->address ?? '';
        $this->contact_name    = $client->contact_name ?? '';
        $this->status          = $client->status;
        $this->notes           = $client->notes ?? '';
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

    public function rules(): array
    {
        $business_id = $this->isSuperAdmin()
            ? $this->business_id
            : auth()->user()?->business_id;

        $rules = [
            'name'            => ['required', 'string', 'max:150'],
            'city_id'         => ['required', 'integer', Rule::exists('cities', 'id')->whereNull('deleted_at')],
            'document_type'   => ['required', Rule::in(['CC', 'NIT', 'CE', 'PA', 'PPT', 'TI'])],
            'document_number' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('clients', 'document_number')
                    ->where('business_id', $business_id)
                    ->ignore($this->client_id),
            ],
            'verification_digit'      => ['nullable', 'string', 'size:1', 'regex:/^\d$/'],
            'person_type'             => ['required', Rule::in([1, 2])],
            'fiscal_responsibilities' => ['required', 'string', Rule::in(array_keys((array) config('dian.fiscal_responsibilities')))],
            'phone'        => ['nullable', 'string', 'max:30'],
            'email'        => ['nullable', 'email', 'max:100'],
            'address'      => ['nullable', 'string', 'max:200'],
            'contact_name' => ['nullable', 'string', 'max:100'],
            'status'       => ['boolean'],
            'notes'        => ['nullable', 'string'],
        ];

        if ($this->isSuperAdmin()) {
            $rules['business_id'] = ['required', 'integer', 'exists:businesses,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'El nombre o razón social es obligatorio.',
            'name.max'               => 'El nombre no puede superar 150 caracteres.',
            'city_id.required'       => 'Debes seleccionar una ciudad.',
            'city_id.exists'         => 'La ciudad seleccionada no es válida.',
            'document_type.required' => 'El tipo de documento es obligatorio.',
            'document_type.in'       => 'El tipo de documento seleccionado no es válido.',
            'document_number.max'    => 'El número de documento no puede superar 30 caracteres.',
            'document_number.unique' => 'Ya existe un cliente con este número de documento.',
            'verification_digit.size'  => 'El dígito de verificación debe ser un solo número.',
            'verification_digit.regex' => 'El dígito de verificación debe ser un número.',
            'person_type.required'     => 'Debes indicar el tipo de persona.',
            'person_type.in'           => 'El tipo de persona seleccionado no es válido.',
            'fiscal_responsibilities.required' => 'Debes indicar la responsabilidad fiscal.',
            'fiscal_responsibilities.in'       => 'La responsabilidad fiscal seleccionada no es válida.',
            'phone.max'              => 'El teléfono no puede superar 30 caracteres.',
            'email.email'            => 'El correo electrónico no es válido.',
            'email.max'              => 'El correo no puede superar 100 caracteres.',
            'address.max'            => 'La dirección no puede superar 200 caracteres.',
            'contact_name.max'       => 'El nombre de contacto no puede superar 100 caracteres.',
            'business_id.required'   => 'Debes seleccionar un comercio.',
            'business_id.exists'     => 'El comercio seleccionado no es válido.',
        ];
    }

    public function isEditing(): bool
    {
        return (bool) $this->client_id;
    }

    /** Dígito de verificación sugerido para el NIT digitado. */
    public function suggestedVerificationDigit(): ?string
    {
        if ($this->document_type !== 'NIT') {
            return null;
        }

        return \App\Support\DianNit::verificationDigit($this->document_number);
    }

    public function validated(): array
    {
        $this->validate();

        return [
            'city_id'         => (int) $this->city_id,
            'name'            => $this->name,
            'document_type'   => $this->document_type,
            'document_number' => $this->document_number ?: null,
            'verification_digit'      => $this->document_type === 'NIT' ? ($this->verification_digit ?: null) : null,
            'person_type'             => $this->person_type,
            'fiscal_responsibilities' => $this->fiscal_responsibilities,
            'phone'           => $this->phone ?: null,
            'email'           => $this->email ?: null,
            'address'         => $this->address ?: null,
            'contact_name'    => $this->contact_name ?: null,
            'status'          => $this->status,
            'notes'           => $this->notes ?: null,
        ];
    }
}
