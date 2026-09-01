<?php

namespace App\Actions\Business;

use App\Models\Participant;
use App\Models\ParticipantRole;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateParticipantAction
{
    use AsAction;

    /**
     * @param  array{
     *     participant_role_id: ?int,
     *     first_name: string,
     *     last_name: string,
     *     email: ?string,
     *     phone_number: ?string,
     *     address: ?string,
     *     status: bool,
     *     document_type: ?string,
     *     document_number: ?string,
     *     city_id: ?int,
     *     country_id: ?int
     * }  $data
     */
    public function handle(int $business_id, ?int $participant_id, array $data): Participant
    {
        abort_unless(
            auth()->user()->can($participant_id ? 'participants.edit' : 'participants.create'),
            403
        );

        $user = auth()->user();

        if (! $user->hasRole('superAdmin')) {
            abort_unless($user->belongsToBusiness($business_id), 403);
        }

        if ($data['participant_role_id'] !== null) {
            $role_ok = ParticipantRole::query()
                ->forAuthUser($user)
                ->whereKey($data['participant_role_id'])
                ->where('business_id', $business_id)
                ->exists();

            abort_unless($role_ok, 422);
        }

        if (filled($data['document_number'] ?? null) && filled($data['document_type'] ?? null)) {
            $document_exists = Participant::query()
                ->where('document_type', $data['document_type'])
                ->where('document_number', $data['document_number'])
                ->when(
                    $participant_id,
                    fn ($query) => $query->whereKeyNot($participant_id)
                )
                ->exists();

            if ($document_exists) {
                throw ValidationException::withMessages([
                    'form.document_number' => 'Ya existe un participante con este tipo y número de documento.',
                ]);
            }
        }

        $attributes = [
            'business_id' => $business_id,
            'participant_role_id' => $data['participant_role_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'address' => $data['address'],
            'status' => $data['status'],
            'document_type' => $data['document_type'],
            'document_number' => $data['document_number'],
            'city_id' => $data['city_id'],
            'country_id' => $data['country_id'],
        ];

        if ($participant_id) {
            $participant = Participant::query()->forAuthUser()->findOrFail($participant_id);
            abort_unless((int) $participant->business_id === (int) $business_id, 403);

            $participant->update($attributes);

            return $participant->fresh();
        }

        return Participant::query()->create($attributes);
    }
}
