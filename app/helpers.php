<?php

if (! function_exists('col_number')) {
    /**
     * Formatea un número con separación de miles y decimales al estilo colombiano.
     * Ejemplo: 1234567.89 → "1.234.567,89"
     */
    function col_number(float|int|null $value, int $decimals = 2): string
    {
        return number_format((float) ($value ?? 0), $decimals, ',', '.');
    }
}

if (! function_exists('col_money')) {
    /**
     * Formatea un valor monetario con símbolo $, 2 decimales y separación colombiana.
     * Ejemplo: 1234.56 → "$1.234,56"
     */
    function col_money(float|int|null $value): string
    {
        return '$' . col_number($value, 2);
    }
}

if (! function_exists('org_term')) {
    /**
     * Traduce un término de UI según el tipo de organización (lang/es/org.php).
     *
     * Sin contexto: usa el negocio del usuario autenticado.
     * Con Business o label (ej. "iglesia"): usa ese tipo de organización.
     */
    function org_term(?string $term, \App\Models\Business|string|null $context = null): string
    {
        static $overrides_cache = [];

        if ($term === null || $term === '') {
            return (string) $term;
        }

        $label = null;

        if ($context instanceof \App\Models\Business) {
            $label = $context->organization_type?->label;
        } elseif (is_string($context) && $context !== '') {
            $label = $context;
        } else {
            $user = auth()->user();

            if ($user) {
                $label = $user->business?->organization_type?->label;
            }
        }

        if (! $label) {
            return $term;
        }

        if (! array_key_exists($label, $overrides_cache)) {
            $overrides = trans("org.{$label}", [], 'es');
            $overrides_cache[$label] = is_array($overrides) ? $overrides : [];
        }

        return $overrides_cache[$label][$term] ?? $term;
    }
}
