<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BusinessLogoStorage
{
    public static function directory(int $business_id): string
    {
        return "logos/business/{$business_id}";
    }

    public static function fileName(string $extension): string
    {
        return 'logo.' . ltrim(strtolower($extension), '.');
    }

    public static function path(int $business_id, string $extension): string
    {
        return self::directory($business_id) . '/' . self::fileName($extension);
    }

    public static function store(int $business_id, UploadedFile $file, ?string $legacy_path = null): string
    {
        self::deleteForBusiness($business_id, $legacy_path);

        $extension = self::resolveExtension($file);
        $stored_path = self::path($business_id, $extension);

        Storage::disk('public')->put($stored_path, $file->get());

        return $stored_path;
    }

    public static function deleteForBusiness(int $business_id, ?string $legacy_path = null): void
    {
        Storage::disk('public')->deleteDirectory(self::directory($business_id));

        if ($legacy_path && ! str_starts_with($legacy_path, self::directory($business_id) . '/')) {
            Storage::disk('public')->delete($legacy_path);
        }
    }

    public static function url(?string $stored_path): ?string
    {
        if (blank($stored_path)) {
            return null;
        }

        $stored_path = str_replace('\\', '/', $stored_path);

        if (! Storage::disk('public')->exists($stored_path)) {
            return null;
        }

        return '/storage/' . ltrim($stored_path, '/');
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
