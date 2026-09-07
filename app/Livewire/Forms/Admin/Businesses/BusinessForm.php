<?php

namespace App\Livewire\Forms\Admin\Businesses;

use App\Models\Business;
use Illuminate\Validation\Rule;
use Livewire\Form;

class BusinessForm extends Form
{
    public ?int $business_id = null;

    public ?int $business_type_id = null;

    public string $name = '';

    public string $nit = '';

    public string $verification_digit = '';

    public int $person_type = 1;

    public string $fiscal_responsibilities = 'R-99-PN';

    public string $postal_code = '';

    public string $phone_number = '';

    public string $email = '';

    public string $address = '';

    public ?int $city_id = null;

    public string $website = '';

    public string $tagline = '';

    public string $tax_regime = '';

    public string $facebook = '';

    public string $instagram = '';

    public string $twitter = '';

    public string $rep_name = '';

    public string $rep_phone = '';

    public string $rep_email = '';

    public bool $status = true;

    public function setBusiness(Business $business): void
    {
        $this->business_id      = $business->id;
        $this->business_type_id = $business->business_type_id;
        $this->name             = $business->name;
        $this->nit              = $business->nit ?? '';
        $this->verification_digit = $business->verification_digit ?? '';
        $this->person_type      = $business->person_type ?? 1;
        $this->fiscal_responsibilities = $business->fiscal_responsibilities ?? 'R-99-PN';
        $this->postal_code      = $business->postal_code ?? '';
        $this->phone_number     = $business->phone_number ?? '';
        $this->email            = $business->email ?? '';
        $this->address          = $business->address ?? '';
        $this->city_id          = $business->city_id;
        $this->website          = $business->website ?? '';
        $this->tagline          = $business->tagline ?? '';
        $this->tax_regime       = $business->tax_regime ?? '';
        $this->facebook         = $business->facebook ?? '';
        $this->instagram        = $business->instagram ?? '';
        $this->twitter          = $business->twitter ?? '';
        $this->status           = (bool) $business->status;

        $rep = is_array($business->representative) ? $business->representative : [];
        $this->rep_name  = $rep['name'] ?? '';
        $this->rep_phone = $rep['phone'] ?? '';
        $this->rep_email = $rep['email'] ?? '';
    }

    public function rules(): array
    {
        return [
            'business_type_id' => [
                'required',
                'integer',
                Rule::exists('business_types', 'id')->where(
                    fn ($q) => $q->where('active', true)->whereNull('deleted_at')
                ),
            ],
            'name'         => ['required', 'string', 'min:3', 'max:150'],
            'nit'          => ['required', 'string', 'max:30', Rule::unique('businesses', 'nit')->ignore($this->business_id)],
            'verification_digit'      => ['nullable', 'string', 'size:1', 'regex:/^\d$/'],
            'person_type'             => ['required', Rule::in([1, 2])],
            'fiscal_responsibilities' => ['required', 'string', Rule::in(array_keys((array) config('dian.fiscal_responsibilities')))],
            'postal_code'             => ['nullable', 'string', 'max:10'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'email'        => ['nullable', 'email', 'max:150'],
            'address'      => ['nullable', 'string', 'max:255'],
            'city_id'      => ['nullable', 'integer', 'exists:cities,id'],
            'website'      => ['nullable', 'url', 'max:255'],
            'tagline'      => ['nullable', 'string', 'max:200'],
            'tax_regime'   => ['nullable', 'string', 'max:80'],
            'facebook'     => ['nullable', 'string', 'max:255'],
            'instagram'    => ['nullable', 'string', 'max:255'],
            'twitter'      => ['nullable', 'string', 'max:255'],
            'rep_name'     => ['nullable', 'string', 'max:150'],
            'rep_phone'    => ['nullable', 'string', 'max:20'],
            'rep_email'    => ['nullable', 'email', 'max:150'],
            'status'       => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'business_type_id.required' => 'Selecciona el tipo de negocio.',
            'business_type_id.exists'   => 'El tipo de negocio seleccionado no es válido.',
            'name.required'             => 'El nombre del negocio es obligatorio.',
            'name.min'                  => 'El nombre debe tener al menos 3 caracteres.',
            'nit.required'              => 'El NIT es obligatorio.',
            'nit.unique'                => 'Ya existe otro negocio con este NIT.',
            'verification_digit.size'   => 'El dígito de verificación debe ser un solo número.',
            'verification_digit.regex'  => 'El dígito de verificación debe ser un número.',
            'person_type.required'      => 'Debes indicar el tipo de persona.',
            'person_type.in'            => 'El tipo de persona seleccionado no es válido.',
            'fiscal_responsibilities.required' => 'Debes indicar la responsabilidad fiscal.',
            'fiscal_responsibilities.in'       => 'La responsabilidad fiscal seleccionada no es válida.',
            'email.email'               => 'El correo no es válido.',
            'website.url'               => 'La URL del sitio web no es válida.',
            'rep_email.email'           => 'El correo del representante no es válido.',
        ];
    }

    public function isEditing(): bool
    {
        return (bool) $this->business_id;
    }

    public function validated(): array
    {
        $this->validate();

        return [
            'business_type_id' => $this->business_type_id,
            'name'             => $this->name,
            'nit'              => $this->nit,
            'verification_digit'      => $this->verification_digit ?: null,
            'person_type'             => $this->person_type,
            'fiscal_responsibilities' => $this->fiscal_responsibilities,
            'postal_code'             => $this->postal_code ?: null,
            'phone_number'     => $this->phone_number ?: null,
            'email'            => $this->email ?: null,
            'address'          => $this->address ?: null,
            'city_id'          => $this->city_id,
            'website'          => $this->website ?: null,
            'tagline'          => $this->tagline ?: null,
            'tax_regime'       => $this->tax_regime ?: null,
            'facebook'         => $this->facebook ?: null,
            'instagram'        => $this->instagram ?: null,
            'twitter'          => $this->twitter ?: null,
            'representative'   => array_filter([
                'name'  => $this->rep_name,
                'phone' => $this->rep_phone,
                'email' => $this->rep_email,
            ]) ?: null,
            'status' => $this->status,
        ];
    }
}
