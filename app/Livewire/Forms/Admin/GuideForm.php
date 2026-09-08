<?php

namespace App\Livewire\Forms\Admin;

use App\Models\Guide;
use Illuminate\Validation\Rule;
use Livewire\Form;

class GuideForm extends Form
{
    public ?int $guide_id = null;

    public string $title = '';

    public string $module = '';

    public string $type = Guide::TYPE_DOCUMENTATION;

    public string $summary = '';

    public string $content = '';

    public string $sort_order = '0';

    public bool $published = true;

    public bool $visible_to_businesses = false;

    public function setGuide(Guide $guide): void
    {
        $this->guide_id   = $guide->id;
        $this->title      = $guide->title;
        $this->module     = $guide->module;
        $this->type       = $guide->type;
        $this->summary    = $guide->summary ?? '';
        $this->content    = $guide->content ?? '';
        $this->sort_order = (string) $guide->sort_order;
        $this->published  = (bool) $guide->published;
        $this->visible_to_businesses = (bool) $guide->visible_to_businesses;
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);

        $this->guide_id   = null;
        $this->title      = '';
        $this->module     = '';
        $this->type       = Guide::TYPE_DOCUMENTATION;
        $this->summary    = '';
        $this->content    = '';
        $this->sort_order = '0';
        $this->published  = true;
        $this->visible_to_businesses = false;
    }

    public function isEditing(): bool
    {
        return (bool) $this->guide_id;
    }

    public function rules(): array
    {
        return [
            'title'      => ['required', 'string', 'max:180'],
            'module'     => ['required', 'string', 'max:80'],
            'type'       => ['required', Rule::in(array_keys(Guide::types()))],
            'summary'    => ['nullable', 'string', 'max:300'],
            'content'    => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'published'  => ['boolean'],
            'visible_to_businesses' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'   => 'Escribe un título para la guía.',
            'module.required'  => 'Indica a qué módulo pertenece.',
            'content.required' => 'La guía no puede quedar vacía.',
            'sort_order.min'   => 'El orden no puede ser negativo.',
        ];
    }

    /** @return array<string, mixed> */
    public function validated(): array
    {
        $this->validate();

        return [
            'title'      => trim($this->title),
            'module'     => trim($this->module),
            'type'       => $this->type,
            'summary'    => trim($this->summary) !== '' ? trim($this->summary) : null,
            'content'    => $this->content,
            'sort_order' => $this->sort_order !== '' ? (int) $this->sort_order : 0,
            'published'  => $this->published,
            'visible_to_businesses' => $this->visible_to_businesses,
        ];
    }
}
