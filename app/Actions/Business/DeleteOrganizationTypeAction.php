<?php

namespace App\Actions\Business;

use App\Models\OrganizationType;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteOrganizationTypeAction
{
    use AsAction;

    public function handle(int $organization_type_id): void
    {
        abort_unless(auth()->user()?->can('organization_types.delete'), 403);

        $organization_type = OrganizationType::query()->findOrFail($organization_type_id);

        if ($organization_type->business_types()->exists()) {
            abort(422, 'No se puede eliminar: hay tipos de negocio asociados.');
        }

        if ($organization_type->businesses()->exists()) {
            abort(422, 'No se puede eliminar: hay negocios asociados a este tipo.');
        }

        $organization_type->delete();
    }
}
