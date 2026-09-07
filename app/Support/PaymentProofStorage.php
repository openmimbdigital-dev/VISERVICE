<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Comprobantes de pago de las suscripciones.
 *
 * Van al disco privado porque contienen información financiera del comercio:
 * se entregan solo a través de una ruta autenticada, nunca por URL directa.
 */
class PaymentProofStorage
{
    public const DISK = 'documents';

    private const FOLDER = 'payment-proofs';

    public static function directory(int $business_id): string
    {
        return self::FOLDER."/{$business_id}";
    }

    public static function store(int $business_id, UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'pdf');
        $path = self::directory($business_id).'/'.Str::uuid().'.'.$extension;

        Storage::disk(self::DISK)->put($path, $file->get());

        return $path;
    }

    public static function exists(?string $path): bool
    {
        return filled($path) && Storage::disk(self::DISK)->exists($path);
    }
}
