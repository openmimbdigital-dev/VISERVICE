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
