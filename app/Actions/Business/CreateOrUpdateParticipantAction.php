<?php

namespace App\Actions\Business;

use App\Models\Participant;
use App\Models\ParticipantRole;
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
     *     document_number: ?int,
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
