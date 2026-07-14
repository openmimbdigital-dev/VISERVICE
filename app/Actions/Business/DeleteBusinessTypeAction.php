<?php

namespace App\Actions\Business;

use App\Models\BusinessType;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteBusinessTypeAction
{
    use AsAction;

    public function handle(int $business_type_id): void
    {
        abort_unless(auth()->user()?->can('business_types.delete'), 403);

        $business_type = BusinessType::query()->findOrFail($business_type_id);

        if ($business_type->businesses()->exists()) {
            abort(422, 'No se puede eliminar: hay negocios asociados a este tipo de negocio.');
        }

        $business_type->delete();
    }
}
