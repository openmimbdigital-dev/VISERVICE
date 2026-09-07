<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageStorage
{
    public static function directory(int $business_id, int $product_id): string
    {
        return "products/business-{$business_id}/{$product_id}";
    }

    public static function store(int $business_id, int $product_id, UploadedFile $file): string
    {
        $extension = self::resolveExtension($file);
        $path = self::directory($business_id, $product_id) . '/' . Str::uuid() . '.' . $extension;

        Storage::disk('public')->put($path, $file->get());

        return $path;
    }

    public static function delete(string $path): void
    {
        Storage::disk('public')->delete($path);
    }

    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $path = str_replace('\\', '/', $path);

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return '/storage/' . ltrim($path, '/');
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
