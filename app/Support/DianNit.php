<?php

namespace App\Support;

class DianNit
{
    /** Factores oficiales DIAN para el cálculo del dígito de verificación. */
    private const WEIGHTS = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];

    /**
     * Devuelve el número de identificación sin puntos, espacios ni dígito de verificación.
     * Acepta formatos como "900.123.456-1", "900123456-1" o "900123456".
     */
    public static function normalize(?string $identification): string
    {
        $identification = trim((string) $identification);

        if ($identification === '') {
            return '';
        }

        // Si trae el DV separado por guion, se descarta el sufijo.
        if (preg_match('/^(.+)-\s*\d\s*$/', $identification, $matches)) {
            $identification = $matches[1];
        }

        return preg_replace('/\D/', '', $identification) ?? '';
    }

    /**
     * Calcula el dígito de verificación DIAN del número de identificación.
     * Devuelve null si el número no es utilizable.
     */
    public static function verificationDigit(?string $identification): ?string
    {
        $number = self::normalize($identification);

        if ($number === '' || strlen($number) > count(self::WEIGHTS)) {
            return null;
        }

        $digits = array_reverse(str_split($number));
        $sum = 0;

        foreach ($digits as $index => $digit) {
            $sum += ((int) $digit) * self::WEIGHTS[$index];
        }

        $remainder = $sum % 11;

        return (string) ($remainder > 1 ? 11 - $remainder : $remainder);
    }

    /**
     * Dígito de verificación almacenado, o calculado si no se ha registrado.
     */
    public static function resolveVerificationDigit(?string $identification, ?string $stored = null): ?string
    {
        $stored = trim((string) $stored);

        return $stored !== '' ? $stored : self::verificationDigit($identification);
    }
}
