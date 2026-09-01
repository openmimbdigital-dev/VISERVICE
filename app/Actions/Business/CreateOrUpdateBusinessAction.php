<?php

namespace App\Actions\Business;

use App\Models\Business;
use App\Models\BusinessType;
use App\Models\City;
use App\Support\BusinessLogoStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateBusinessAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(
        ?int $business_id,
        array $data,
        ?UploadedFile $logo_file = null,
        bool $remove_logo = false,
        bool $update_status = false
    ): Business {
        abort_unless(
            auth()->user()?->can($business_id ? 'businesses.edit' : 'businesses.create'),
            403
        );

        $business_type = BusinessType::query()
            ->where('active', true)
            ->findOrFail($data['business_type_id']);

        $country_id = null;

        if (! empty($data['city_id'])) {
            $country_id = City::query()->whereKey($data['city_id'])->value('country_id');
        }

        $attributes = [
            'name'                 => $data['name'],
            'nit'                  => $data['nit'],
            'business_type_id'     => $business_type->id,
            'organization_type_id' => $business_type->organization_type_id,
            'phone_number'         => $data['phone_number'],
            'email'                => $data['email'],
            'address'              => $data['address'],
            'city_id'              => $data['city_id'],
            'country_id'           => $country_id,
            'website'              => $data['website'],
            'tagline'              => $data['tagline'] ?? null,
            'tax_regime'           => $data['tax_regime'] ?? null,
            'facebook'             => $data['facebook'],
            'instagram'            => $data['instagram'],
            'twitter'              => $data['twitter'],
            'representative'       => $data['representative'],
        ];

        if ($business_id) {
            $business = Business::query()->forAuthUser()->findOrFail($business_id);

            if ($update_status) {
                $attributes['status'] = $data['status'];
            }

            $business->update($attributes);

            $this->syncLogo($business, $logo_file, $remove_logo);

            return $business->fresh();
        }

        $slug = Str::slug($data['name']);

        if (Business::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::lower(Str::random(5));
        }

        $attributes['slug']   = $slug;
        $attributes['status'] = $data['status'];
        $attributes['logo']   = null;

        $business = Business::create($attributes);

        SyncBusinessAccessFromOrganizationTypeAction::run($business);

        $user = auth()->user();

        if ($user && ! $user->hasRole('superAdmin') && ! $user->belongsToBusiness($business->id)) {
            $user->attachBusiness((int) $business->id, is_primary: $user->businessIds() === []);
        }

        $this->syncLogo($business, $logo_file, $remove_logo);

        return $business->fresh();
    }

    private function syncLogo(Business $business, ?UploadedFile $logo_file, bool $remove_logo): void
    {
        if ($remove_logo) {
            BusinessLogoStorage::deleteForBusiness($business->id, $business->logo);
            $business->update(['logo' => null]);

            return;
        }

        if ($logo_file) {
            $path = BusinessLogoStorage::store($business->id, $logo_file, $business->logo);
            $business->update(['logo' => $path]);
        }
    }
}
