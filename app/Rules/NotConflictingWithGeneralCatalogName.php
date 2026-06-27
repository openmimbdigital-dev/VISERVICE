<?php

namespace App\Rules;

use App\Actions\Settings\Equipment\CreateOrUpdateBrandAction;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class NotConflictingWithGeneralCatalogName implements ValidationRule
{
    /**
     * @param  class-string<Model>  $model_class
     */
    public function __construct(
        private string $model_class,
        private ?int $ignore_id = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $name = mb_strtolower(trim((string) $value));

        if ($name === '') {
            return;
        }

        $label = CreateOrUpdateBrandAction::normalizeLabel((string) $value);

        if ($label === '') {
            $fail('El nombre debe contener al menos una letra o número.');

            return;
        }

        $general_scope = fn ($query) => $query
            ->whereNull('business_id')
            ->where('general', true);

        $name_query = $this->model_class::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [$name]);
        $general_scope($name_query);

        if ($this->ignore_id) {
            $name_query->where('id', '!=', $this->ignore_id);
        }

        if ($name_query->exists()) {
            $fail('Ya existe un registro general del catálogo con este nombre.');

            return;
        }

        $label_query = $this->model_class::query()->where('label', $label);
        $general_scope($label_query);

        if ($this->ignore_id) {
            $label_query->where('id', '!=', $this->ignore_id);
        }

        if ($label_query->exists()) {
            $fail('Ya existe un registro general del catálogo con un nombre equivalente.');
        }
    }
}
