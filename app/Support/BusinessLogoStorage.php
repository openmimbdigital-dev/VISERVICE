<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Logos de los negocios dentro del volumen de datos.
 *
 * Viven en el disco público «media», bajo business-logos/{negocio}, porque se
 * muestran en la interfaz y en los PDF.
 */
class BusinessLogoStorage
{
    public const DISK = 'media';

    private const FOLDER = 'business-logos';

    public static function directory(int $business_id): string
    {
        return self::FOLDER."/{$business_id}";
    }

    public static function fileName(string $extension): string
    {
        return 'logo.'.ltrim(strtolower($extension), '.');
    }

    public static function path(int $business_id, string $extension): string
    {
        return self::directory($business_id).'/'.self::fileName($extension);
    }

    public static function store(int $business_id, UploadedFile $file, ?string $legacy_path = null): string
    {
        self::deleteForBusiness($business_id, $legacy_path);

        $extension = self::resolveExtension($file);
        $stored_path = self::path($business_id, $extension);

        Storage::disk(self::DISK)->put($stored_path, $file->get());

        return $stored_path;
    }

    public static function deleteForBusiness(int $business_id, ?string $legacy_path = null): void
    {
        Storage::disk(self::DISK)->deleteDirectory(self::directory($business_id));

        // Los logos anteriores quedaron sueltos en otras carpetas: se borran por su ruta.
        if ($legacy_path && ! str_starts_with($legacy_path, self::directory($business_id).'/')) {
            Storage::disk(self::DISK)->delete($legacy_path);
        }
    }

    public static function url(?string $stored_path): ?string
    {
        if (blank($stored_path)) {
            return null;
        }

        $stored_path = str_replace('\\', '/', $stored_path);

        if (! Storage::disk(self::DISK)->exists($stored_path)) {
            return null;
        }

        return Storage::disk(self::DISK)->url($stored_path);
    }

    private static function resolveExtension(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'png';

        return match (strtolower($extension)) {
            'jpeg', 'jfif' => 'jpg',
            default => strtolower($extension),
        };
    }
}
