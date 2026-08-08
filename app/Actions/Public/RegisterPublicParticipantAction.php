<?php

namespace App\Actions\Public;

use App\Models\Business;
use App\Models\Participant;
use App\Models\ParticipantRole;
use Lorisleiva\Actions\Concerns\AsAction;

class RegisterPublicParticipantAction
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
     *     document_type: ?string,
     *     document_number: ?string,
     *     city_id: ?int,
     *     country_id: ?int
     * }  $data
     */
    public function handle(Business $business, array $data): Participant
    {
        abort_unless((bool) $business->status && $business->deleted_at === null, 404);

        if ($data['participant_role_id'] !== null) {
            $role_ok = ParticipantRole::query()
                ->whereKey($data['participant_role_id'])
                ->where('business_id', $business->id)
                ->where('active', true)
                ->whereNull('deleted_at')
                ->exists();

            abort_unless($role_ok, 422);
        }

        return Participant::query()->create([
            'business_id' => $business->id,
            'participant_role_id' => $data['participant_role_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'address' => $data['address'],
            'status' => true,
            'document_type' => $data['document_type'],
            'document_number' => $data['document_number'],
            'city_id' => $data['city_id'],
            'country_id' => $data['country_id'],
        ]);
    }
}
