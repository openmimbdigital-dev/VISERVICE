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
     * Traduce un término de UI según el tipo de organización del negocio
     * actual del usuario (lang/es/org.php). Si no hay traducción definida,
     * devuelve el término original.
     * Ejemplo (negocio tipo iglesia): org_term('Negocios') → "Iglesias"
     */
    function org_term(?string $term): string
    {
        static $overrides_cache = [];

        if ($term === null || $term === '') {
            return (string) $term;
        }

        $user = auth()->user();

        if (! $user) {
            return $term;
        }

        $cache_key = (string) ($user->business_id ?? 0);

        if (! array_key_exists($cache_key, $overrides_cache)) {
            $label = $user->business?->organization_type?->label;
            $overrides = $label ? trans("org.{$label}", [], 'es') : null;
            $overrides_cache[$cache_key] = is_array($overrides) ? $overrides : [];
        }

        return $overrides_cache[$cache_key][$term] ?? $term;
    }
}
