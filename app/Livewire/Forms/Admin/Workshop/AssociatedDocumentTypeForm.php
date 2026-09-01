<?php

namespace App\Livewire\Forms\Admin\Workshop;

use App\Models\AssociatedDocumentType;
use App\Support\CurrentBusiness;
use Illuminate\Validation\Rule;
use Livewire\Form;

class AssociatedDocumentTypeForm extends Form
{
    public ?int $document_type_id = null;

    public ?int $business_id = null;

    public string $name = '';

    public bool $active = true;

    public bool $document_send = false;

    public function setDocumentType(AssociatedDocumentType $type): void
    {
        $this->document_type_id = $type->id;
        $this->business_id      = (int) $type->business_id;
        $this->name             = $type->name;
        $this->active           = $type->active;
        $this->document_send    = $type->document_send;
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->document_type_id = null;
        $this->business_id      = null;
        $this->name             = '';
        $this->active           = true;
        $this->document_send    = false;
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('superAdmin') ?? false;
    }

    public function isEditing(): bool
    {
        return (bool) $this->document_type_id;
    }

    public function resolvedBusinessId(): int
    {
        if ($this->isSuperAdmin()) {
            return (int) ($this->business_id
                ?: CurrentBusiness::id()
                ?: auth()->user()?->business_id);
        }

        return (int) (CurrentBusiness::id() ?? auth()->user()?->business_id);
    }

    public function rules(): array
    {
        $business_id = $this->resolvedBusinessId();

        return [
            'business_id' => $this->isSuperAdmin()
                ? ['required', 'integer', Rule::exists('businesses', 'id')->whereNull('deleted_at')]
                : ['nullable'],
            'name' => [
                'required',
                'string',
                'max:150',
                function (string $attribute, mixed $value, \Closure $fail) use ($business_id) {
                    $key = AssociatedDocumentType::makeKeyFromName((string) $value);

                    $exists = AssociatedDocumentType::query()
                        ->where('business_id', $business_id)
                        ->where('key', $key)
                        ->when($this->document_type_id, fn ($q) => $q->where('id', '!=', $this->document_type_id))
                        ->exists();

                    if ($exists) {
                        $fail('Ya existe un documento asociado con este nombre.');
                    }
                },
            ],
            'active'        => ['boolean'],
            'document_send' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'business_id.required' => 'El negocio es obligatorio.',
            'name.required'        => 'El nombre del documento es obligatorio.',
            'name.max'             => 'El nombre no puede superar 150 caracteres.',
        ];
    }

    /** @return array{business_id: int, name: string, active: bool, document_send: bool} */
    public function validated(): array
    {
        if ($this->isSuperAdmin() && ! $this->business_id) {
            $this->business_id = CurrentBusiness::id() ?? auth()->user()?->business_id;
        }

        $this->validate();

        return [
            'business_id'   => $this->resolvedBusinessId(),
            'name'          => trim($this->name),
            'active'        => $this->active,
            'document_send' => $this->document_send,
        ];
    }
}
