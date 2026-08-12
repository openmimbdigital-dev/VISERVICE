<?php

namespace App\Support\Public;

use App\Models\Business;
use App\Models\GeneralConfig;
use App\Models\Participant;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class ParticipantsPortalSession
{
    public static function sessionKey(int $business_id): string
    {
        return "participants_portal.{$business_id}";
    }

    public static function isAuthenticated(Business $business): bool
    {
        return self::participant($business) !== null;
    }

    public static function participant(Business $business): ?Participant
    {
        $payload = session(self::sessionKey((int) $business->id));

        if (! is_array($payload) || empty($payload['participant_id'])) {
            return null;
        }

        return Participant::query()
            ->where('business_id', $business->id)
            ->whereKey((int) $payload['participant_id'])
            ->where('status', true)
            ->whereNull('deleted_at')
            ->first();
    }

    public static function authenticate(Business $business, string $pin, string $document_type, string $document_number): ?Participant
    {
        if (! self::verifyPin($business, $pin)) {
            return null;
        }

        $participant = Participant::query()
            ->where('business_id', $business->id)
            ->where('document_type', $document_type)
            ->where('document_number', trim($document_number))
            ->where('status', true)
            ->whereNull('deleted_at')
            ->first();

        if ($participant === null) {
            return null;
        }

        session()->put(self::sessionKey((int) $business->id), [
            'participant_id' => $participant->id,
            'authenticated_at' => now()->timestamp,
        ]);

        return $participant;
    }

    public static function logout(Business $business): void
    {
        session()->forget(self::sessionKey((int) $business->id));
    }

    public static function verifyPin(Business $business, string $pin): bool
    {
        $config = self::pinConfig($business);

        if ($config === null || blank($config->value)) {
            return false;
        }

        $value = (string) $config->value;
        $decrypted = self::decryptedPin($business);

        if ($decrypted !== null) {
            return hash_equals($decrypted, $pin);
        }

        return self::verifyLegacyHashedPin($value, $pin);
    }

    public static function decryptedPin(Business $business): ?string
    {
        $config = self::pinConfig($business);

        if ($config === null || blank($config->value)) {
            return null;
        }

        $value = (string) $config->value;

        if (str_starts_with($value, '$2y$') || str_starts_with($value, '$2a$')) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return null;
        }
    }

    /** Supports legacy bcrypt hashes saved before encryption was introduced. */
    private static function verifyLegacyHashedPin(string $stored, string $pin): bool
    {
        if (! str_starts_with($stored, '$2y$') && ! str_starts_with($stored, '$2a$')) {
            return false;
        }

        return Hash::check($pin, $stored);
    }

    public static function pinConfigured(Business $business): bool
    {
        $config = self::pinConfig($business);

        return $config !== null && filled($config->value);
    }

    public static function pinConfig(Business $business): ?GeneralConfig
    {
        return GeneralConfig::query()
            ->where('business_id', $business->id)
            ->participantsPortalPin()
            ->first();
    }
}
