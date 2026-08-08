<?php

namespace App\Support\Public;

use App\Models\Business;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * Token opaco URL-safe para identificar un negocio en rutas públicas
 * sin exponer el business_id numérico.
 */
class BusinessPublicId
{
    public static function encode(int $business_id): string
    {
        $encrypted = Crypt::encryptString((string) $business_id);

        return rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');
    }

    public static function decode(string $token): ?int
    {
        if ($token === '') {
            return null;
        }

        try {
            $padded = strtr($token, '-_', '+/');
            $pad_length = (4 - strlen($padded) % 4) % 4;
            $binary = base64_decode($padded.str_repeat('=', $pad_length), true);

            if ($binary === false) {
                return null;
            }

            $business_id = (int) Crypt::decryptString($binary);

            return $business_id > 0 ? $business_id : null;
        } catch (DecryptException) {
            return null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function resolveBusiness(string $token): ?Business
    {
        $business_id = self::decode($token);

        if ($business_id === null) {
            return null;
        }

        return Business::query()
            ->whereKey($business_id)
            ->where('status', true)
            ->whereNull('deleted_at')
            ->first();
    }
}
