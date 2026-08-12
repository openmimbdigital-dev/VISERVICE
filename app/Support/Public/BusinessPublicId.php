<?php

namespace App\Support\Public;

use App\Models\Business;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * Token opaco URL-safe para identificar un negocio en rutas públicas
 * sin exponer el business_id numérico.
 *
 * Formato compacto (~11 caracteres): 4 bytes ofuscados + 4 bytes HMAC truncado.
 */
class BusinessPublicId
{
    private const PAYLOAD_LENGTH = 4;

    private const MAC_LENGTH = 4;

    public static function encode(int $business_id): string
    {
        if ($business_id <= 0 || $business_id > 0xFFFFFFFF) {
            throw new \InvalidArgumentException("Invalid business id [{$business_id}].");
        }

        $payload = pack('N', $business_id);
        $obfuscated = self::obfuscate($payload);
        $mac = self::sign($obfuscated);

        return self::base64UrlEncode($obfuscated.$mac);
    }

    public static function decode(string $token): ?int
    {
        if ($token === '') {
            return null;
        }

        $business_id = self::decodeCompact($token);

        if ($business_id !== null) {
            return $business_id;
        }

        return self::decodeLegacy($token);
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

    private static function decodeCompact(string $token): ?int
    {
        $binary = self::base64UrlDecode($token);

        if ($binary === null || strlen($binary) !== self::PAYLOAD_LENGTH + self::MAC_LENGTH) {
            return null;
        }

        $obfuscated = substr($binary, 0, self::PAYLOAD_LENGTH);
        $mac = substr($binary, self::PAYLOAD_LENGTH, self::MAC_LENGTH);

        if (! hash_equals(self::sign($obfuscated), $mac)) {
            return null;
        }

        $payload = self::deobfuscate($obfuscated);

        if (strlen($payload) !== self::PAYLOAD_LENGTH) {
            return null;
        }

        $business_id = unpack('N', $payload)[1];

        return $business_id > 0 ? (int) $business_id : null;
    }

    /** @deprecated Tokens largos generados con Crypt::encryptString. */
    private static function decodeLegacy(string $token): ?int
    {
        if (strlen($token) <= self::PAYLOAD_LENGTH + self::MAC_LENGTH + 2) {
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

    private static function obfuscate(string $payload): string
    {
        $key = self::obfuscationKey();
        $obfuscated = '';

        for ($i = 0; $i < self::PAYLOAD_LENGTH; $i++) {
            $obfuscated .= chr(ord($payload[$i]) ^ ord($key[$i]));
        }

        return $obfuscated;
    }

    private static function deobfuscate(string $obfuscated): string
    {
        return self::obfuscate($obfuscated);
    }

    private static function sign(string $obfuscated): string
    {
        return substr(hash_hmac('sha256', $obfuscated, self::macKey(), true), 0, self::MAC_LENGTH);
    }

    private static function obfuscationKey(): string
    {
        return substr(hash('sha256', config('app.key').'|business_public_id', true), 0, self::PAYLOAD_LENGTH);
    }

    private static function macKey(): string
    {
        return hash('sha256', config('app.key').'|business_public_id_mac', true);
    }

    private static function base64UrlEncode(string $binary): string
    {
        return rtrim(strtr(base64_encode($binary), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $token): ?string
    {
        $padded = strtr($token, '-_', '+/');
        $pad_length = (4 - strlen($padded) % 4) % 4;
        $binary = base64_decode($padded.str_repeat('=', $pad_length), true);

        return $binary === false ? null : $binary;
    }
}
