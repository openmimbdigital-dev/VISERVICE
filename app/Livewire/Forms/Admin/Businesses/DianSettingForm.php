<?php

namespace App\Livewire\Forms\Admin\Businesses;

use App\Models\Business;
use App\Models\BusinessDianSetting;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Form;

class DianSettingForm extends Form
{
    public ?int $dian_setting_id = null;

    public ?int $business_id = null;

    public string $environment = 'test';

    public string $tr_tipo_id = '';

    public string $cfg_lote_id = '';

    public string $resolution_number = '';

    public string $prefix = '';

    public string $range_from = '';

    public string $range_to = '';

    public string $valid_from = '';

    public string $valid_to = '';

    public string $technical_key = '';

    public string $next_consecutive = '';

    public string $software_id = '';

    public string $software_pin = '';

    public bool $notify_customer = true;

    public bool $include_pdf = true;

    public bool $include_xml = true;

    public bool $include_attachments = false;

    public bool $active = false;

    public string $notes = '';

    public function setDianSetting(BusinessDianSetting $setting): void
    {
        $this->dian_setting_id     = $setting->id;
        $this->business_id         = $setting->business_id;
        $this->environment         = $setting->environment;
        $this->tr_tipo_id          = (string) ($setting->tr_tipo_id ?? '');
        $this->cfg_lote_id         = (string) ($setting->cfg_lote_id ?? '');
        $this->resolution_number   = (string) ($setting->resolution_number ?? '');
        $this->prefix              = (string) ($setting->prefix ?? '');
        $this->range_from          = (string) ($setting->range_from ?? '');
        $this->range_to            = (string) ($setting->range_to ?? '');
        $this->valid_from          = $setting->valid_from?->format('Y-m-d') ?? '';
        $this->valid_to            = $setting->valid_to?->format('Y-m-d') ?? '';
        // Los secretos no se devuelven al formulario: en blanco significa "conservar el actual".
        $this->technical_key       = '';
        $this->next_consecutive    = (string) ($setting->next_consecutive ?? '');
        $this->software_id         = (string) ($setting->software_id ?? '');
        $this->software_pin        = '';
        $this->notify_customer     = $setting->notify_customer;
        $this->include_pdf         = $setting->include_pdf;
        $this->include_xml         = $setting->include_xml;
        $this->include_attachments = $setting->include_attachments;
        $this->active              = $setting->active;
        $this->notes               = (string) ($setting->notes ?? '');
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->dian_setting_id     = null;
        $this->business_id         = null;
        $this->environment         = 'test';
        $this->tr_tipo_id          = '';
        $this->cfg_lote_id         = '';
        $this->resolution_number   = '';
        $this->prefix              = '';
        $this->range_from          = '';
        $this->range_to            = '';
        $this->valid_from          = '';
        $this->valid_to            = '';
        $this->technical_key       = '';
        $this->next_consecutive    = '';
        $this->software_id         = '';
        $this->software_pin        = '';
        $this->notify_customer     = true;
        $this->include_pdf         = true;
        $this->include_xml         = true;
        $this->include_attachments = false;
        $this->active              = false;
        $this->notes               = '';
    }

    public function isEditing(): bool
    {
        return (bool) $this->dian_setting_id;
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

        return (int) (auth()->user()->businessIds()[0] ?? 0);
    }

    public function getBusinesses(): Collection
    {
        if (! $this->isSuperAdmin()) {
            return collect();
        }

        return Business::query()
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function rules(): array
    {
        $rules = [
            'environment'         => ['required', Rule::in(['test', 'production'])],
            'tr_tipo_id'          => ['nullable', 'numeric', 'min:1'],
            'cfg_lote_id'         => ['nullable', 'numeric', 'min:1'],
            'resolution_number'   => ['nullable', 'string', 'max:30'],
            'prefix'              => ['nullable', 'string', 'max:10', 'regex:/^[A-Za-z0-9]+$/'],
            'range_from'          => ['nullable', 'numeric', 'min:1'],
            'range_to'            => ['nullable', 'numeric', 'min:1', 'gte:range_from'],
            'valid_from'          => ['nullable', 'date'],
            'valid_to'            => ['nullable', 'date', 'after_or_equal:valid_from'],
            'technical_key'       => ['nullable', 'string', 'max:255'],
            'next_consecutive'    => ['nullable', 'numeric', 'min:1'],
            'software_id'         => ['nullable', 'string', 'max:255'],
            'software_pin'        => ['nullable', 'string', 'max:255'],
            'notify_customer'     => ['boolean'],
            'include_pdf'         => ['boolean'],
            'include_xml'         => ['boolean'],
            'include_attachments' => ['boolean'],
            'active'              => ['boolean'],
            'notes'               => ['nullable', 'string', 'max:2000'],
        ];

        if ($this->isSuperAdmin()) {
            $rules['business_id'] = [
                'required',
                'integer',
                'exists:businesses,id',
                Rule::unique('business_dian_settings', 'business_id')
                    ->whereNull('deleted_at')
                    ->ignore($this->dian_setting_id),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'business_id.required'    => 'Debe seleccionar un negocio.',
            'business_id.unique'      => 'Este negocio ya tiene una configuración de facturación electrónica.',
            'environment.required'    => 'Debe seleccionar el entorno.',
            'environment.in'          => 'El entorno seleccionado no es válido.',
            'tr_tipo_id.numeric'      => 'El identificador de perfil debe ser numérico.',
            'prefix.regex'            => 'El prefijo solo admite letras y números.',
            'range_to.gte'            => 'El rango final debe ser mayor o igual al inicial.',
            'valid_to.after_or_equal' => 'La fecha final de vigencia debe ser posterior a la inicial.',
            'next_consecutive.min'    => 'El próximo consecutivo debe ser mayor a cero.',
        ];
    }

    /** @return array<string, mixed> */
    public function validated(): array
    {
        $this->validate();

        $data = [
            'business_id'         => $this->resolvedBusinessId(),
            'environment'         => $this->environment,
            'tr_tipo_id'          => $this->tr_tipo_id !== '' ? (int) $this->tr_tipo_id : null,
            'cfg_lote_id'         => $this->cfg_lote_id !== '' ? (int) $this->cfg_lote_id : null,
            'resolution_number'   => $this->nullIfBlank($this->resolution_number),
            'prefix'              => $this->prefix !== '' ? mb_strtoupper(trim($this->prefix)) : null,
            'range_from'          => $this->range_from !== '' ? (int) $this->range_from : null,
            'range_to'            => $this->range_to !== '' ? (int) $this->range_to : null,
            'valid_from'          => $this->nullIfBlank($this->valid_from),
            'valid_to'            => $this->nullIfBlank($this->valid_to),
            'technical_key'       => $this->nullIfBlank($this->technical_key),
            'next_consecutive'    => $this->next_consecutive !== '' ? (int) $this->next_consecutive : null,
            'software_id'         => $this->nullIfBlank($this->software_id),
            'software_pin'        => $this->nullIfBlank($this->software_pin),
            'notify_customer'     => $this->notify_customer,
            'include_pdf'         => $this->include_pdf,
            'include_xml'         => $this->include_xml,
            'include_attachments' => $this->include_attachments,
            'active'              => $this->active,
            'notes'               => $this->nullIfBlank($this->notes),
        ];

        return $data;
    }

    private function nullIfBlank(string $value): ?string
    {
        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
