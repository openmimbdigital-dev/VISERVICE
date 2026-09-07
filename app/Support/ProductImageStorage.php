<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Imágenes de productos dentro del volumen de datos.
 *
 * Viven en el disco público «media», bajo products/business-{id}/{producto},
 * de modo que el servidor las entrega directamente sin pasar por PHP.
 */
class ProductImageStorage
{
    public const DISK = 'media';

    private const FOLDER = 'products';

    public static function directory(int $business_id, int $product_id): string
    {
        return self::FOLDER."/business-{$business_id}/{$product_id}";
    }

    public static function store(int $business_id, int $product_id, UploadedFile $file): string
    {
        $extension = self::resolveExtension($file);
        $path = self::directory($business_id, $product_id).'/'.Str::uuid().'.'.$extension;

        Storage::disk(self::DISK)->put($path, $file->get());

        return $path;
    }

    public static function delete(string $path): void
    {
        Storage::disk(self::DISK)->delete($path);
    }

    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $path = str_replace('\\', '/', $path);

        if (! Storage::disk(self::DISK)->exists($path)) {
            return null;
        }

        return Storage::disk(self::DISK)->url($path);
    }

    private static function resolveExtension(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg';

        return match (strtolower($extension)) {
            'jpeg', 'jfif' => 'jpg',
            default => strtolower($extension),
        };
    }
}
