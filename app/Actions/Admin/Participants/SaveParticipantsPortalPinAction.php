<?php

namespace App\Actions\Admin\Participants;

use App\Models\Business;
use App\Models\GeneralConfig;
use Illuminate\Support\Facades\Crypt;
use Lorisleiva\Actions\Concerns\AsAction;

class SaveParticipantsPortalPinAction
{
    use AsAction;

    public function handle(Business $business, string $pin): GeneralConfig
    {
        abort_unless(auth()->user()?->can('participants.edit'), 403);

        $user = auth()->user();

        if (! $user->hasRole('superAdmin')) {
            abort_unless(in_array((int) $business->id, $user->businessIds(), true), 403);
        }

        $config = GeneralConfig::query()
            ->where('business_id', $business->id)
            ->participantsPortalPin()
            ->first();

        $attributes = [
            'business_id' => $business->id,
            'key' => GeneralConfig::KEY_PUBLIC_PARTICIPANTS_PORTAL_PIN,
            'label' => GeneralConfig::LABEL_PUBLIC_PARTICIPANTS_PORTAL_PIN,
            'value' => Crypt::encryptString($pin),
        ];

        if ($config !== null) {
            $config->update($attributes);

            return $config->fresh();
        }

        return $business->generalConfigs()->create($attributes);
    }
}
