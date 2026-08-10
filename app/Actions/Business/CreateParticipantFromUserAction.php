<?php

namespace App\Actions\Business;

use App\Models\Participant;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateParticipantFromUserAction
{
    use AsAction;

    /**
     * Crea un participante del negocio con los datos del usuario.
     * Si ya existe uno con el mismo email en el negocio, lo reutiliza.
     */
    public function handle(User $user, int $business_id): Participant
    {
        if (filled($user->email)) {
            $existing = Participant::query()
                ->where('business_id', $business_id)
                ->where('email', $user->email)
                ->whereNull('deleted_at')
                ->first();

            if ($existing !== null) {
                return $existing;
            }
        }

        $document_type = $user->document_type;

        if (is_object($document_type) && property_exists($document_type, 'value')) {
            $document_type = $document_type->value;
        }

        return Participant::query()->create([
            'business_id' => $business_id,
            'participant_role_id' => null,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'address' => $user->address,
            'profile_photo' => $user->profile_photo,
            'status' => (bool) $user->status,
            'document_type' => $document_type ?: null,
            'document_number' => $user->document_number !== null
                ? (string) $user->document_number
                : null,
            'city_id' => $user->city_id,
            'country_id' => $user->country_id,
        ]);
    }
}
