<?php

namespace App\Support;

use App\Enums\AttributeType;
use App\Models\Attribute;
use App\Models\AttributeEquipmentType;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class DynamicAttributeValidator
{
    /**
     * @param  Collection<int, AttributeEquipmentType>  $links
     */
    public function __construct(
        protected Collection $links,
        protected bool $is_creating = true,
        protected string $prefix = 'attribute_values',
    ) {}

    public function rules(): array
    {
        $rules = [];

        foreach ($this->links as $link) {
            $attribute = $link->attribute;
            $key       = "{$this->prefix}.{$attribute->id}";

            $rules[$key] = $this->rulesForAttribute($attribute);

            if ($attribute->type === AttributeType::CHECKBOX) {
                $rules["{$key}.*"] = [
                    'string',
                    Rule::in($this->optionValues($attribute)),
                ];
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [];

        foreach ($this->links as $link) {
            $name = $link->attribute->name;
            $id   = $link->attribute->id;

            $messages["{$this->prefix}.{$id}.required"]   = "El campo «{$name}» es obligatorio.";
            $messages["{$this->prefix}.{$id}.numeric"]    = "El campo «{$name}» debe ser numérico.";
            $messages["{$this->prefix}.{$id}.min"]        = "El campo «{$name}» no alcanza el valor mínimo.";
            $messages["{$this->prefix}.{$id}.max"]        = "El campo «{$name}» supera el valor máximo.";
            $messages["{$this->prefix}.{$id}.in"]         = "El valor seleccionado en «{$name}» no es válido.";
            $messages["{$this->prefix}.{$id}.regex"]      = "El color en «{$name}» no es válido.";
            $messages["{$this->prefix}.{$id}.*.in"]       = "Una opción de «{$name}» no es válida.";
        }

        return $messages;
    }

    /**
     * @return array<int, mixed>
     */
    public function normalize(array $raw_values): array
    {
        $normalized = [];

        foreach ($this->links as $link) {
            $attribute = $link->attribute;
            $raw       = $raw_values[$attribute->id] ?? null;

            if ($attribute->type === AttributeType::CHECKBOX) {
                $normalized[$attribute->id] = collect(is_array($raw) ? $raw : [])
                    ->map(fn ($v) => (string) $v)
                    ->filter()
                    ->values()
                    ->all();

                continue;
            }

            if ($attribute->type === AttributeType::NUMBER) {
                $normalized[$attribute->id] = ($raw === null || $raw === '')
                    ? null
                    : (float) $raw;

                continue;
            }

            $normalized[$attribute->id] = is_string($raw) ? trim($raw) : $raw;
        }

        return $normalized;
    }

    /** @return list<string> */
    protected function rulesForAttribute(Attribute $attribute): array
    {
        $optional_on_create = $this->is_creating && $attribute->nullable_creation;
        $is_required        = $attribute->required && ! $optional_on_create;

        $rules = [$is_required ? 'required' : 'nullable'];

        return match ($attribute->type) {
            AttributeType::TEXT     => array_merge($rules, ['string', 'max:255']),
            AttributeType::TEXTAREA => array_merge($rules, ['string', 'max:2000']),
            AttributeType::NUMBER   => $this->numberRules($rules, $attribute),
            AttributeType::SELECT,
            AttributeType::RADIO    => array_merge($rules, ['string', Rule::in($this->optionValues($attribute))]),
            AttributeType::CHECKBOX => array_merge($rules, ['array']),
            AttributeType::COLOR    => array_merge($rules, [
                'string',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ]),
        };
    }

    /** @return list<string|Rule> */
    protected function numberRules(array $rules, Attribute $attribute): array
    {
        $rules[] = 'numeric';

        if ($attribute->min_value !== null) {
            $rules[] = 'min:' . $attribute->min_value;
        }

        if ($attribute->max_value !== null) {
            $rules[] = 'max:' . $attribute->max_value;
        }

        return $rules;
    }

    /** @return list<string> */
    protected function optionValues(Attribute $attribute): array
    {
        return collect($attribute->options ?? [])
            ->pluck('value')
            ->filter()
            ->map(fn ($v) => (string) $v)
            ->values()
            ->all();
    }
}
