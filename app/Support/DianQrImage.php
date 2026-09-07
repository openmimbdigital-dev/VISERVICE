<?php

namespace App\Support;

use Throwable;

/**
 * Genera el QR de la representación gráfica de la factura electrónica.
 *
 * Devuelve null si el paquete de códigos QR no está instalado, de modo que el
 * PDF siga generándose (mostrando solo el CUFE) en lugar de fallar.
 */
class DianQrImage
{
    private const GENERATOR = \SimpleSoftwareIO\QrCode\Generator::class;

    public static function isAvailable(): bool
    {
        return class_exists(self::GENERATOR);
    }

    /** Data URI PNG listo para incrustar en el PDF. */
    public static function dataUri(?string $content, int $size = 120): ?string
    {
        if (blank($content) || ! self::isAvailable()) {
            return null;
        }

        try {
            $generator = new (self::GENERATOR)();
            $png = (string) $generator->format('png')->size($size)->margin(1)->generate($content);
        } catch (Throwable) {
            return null;
        }

        return $png !== '' ? 'data:image/png;base64,'.base64_encode($png) : null;
    }
}
