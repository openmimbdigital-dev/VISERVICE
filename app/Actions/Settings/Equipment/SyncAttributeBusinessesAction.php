<?php

namespace App\Actions\Settings\Equipment;

use App\Models\Attribute;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncAttributeBusinessesAction
{
    use AsAction;

    /**
     * Sincroniza la relación N:N entre un atributo y los negocios.
     *
     * @param  array<int>  $business_ids
     */
    public function handle(Attribute $attribute, array $business_ids, bool $general): void
    {
        if ($general) {
            $attribute->businesses()->detach();

            return;
        }

        if (auth()->user()?->hasRole('superAdmin')) {
            $attribute->businesses()->sync(
                collect($business_ids)->map(fn ($id) => (int) $id)->unique()->values()->all()
            );

            return;
        }

        $business_id = auth()->user()?->business_id;

        abort_unless($business_id, 403, 'Debes pertenecer a un negocio para gestionar atributos.');

        $attribute->businesses()->sync([(int) $business_id]);
    }
}
