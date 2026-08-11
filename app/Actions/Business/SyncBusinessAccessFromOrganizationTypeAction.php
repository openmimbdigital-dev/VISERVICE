<?php

namespace App\Actions\Business;

use App\Models\Business;
use App\Support\BusinessAccess;
use App\Support\BusinessModuleAccess;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncBusinessAccessFromOrganizationTypeAction
{
    use AsAction;

    /**
     * Copia roles, permisos y módulos desde un negocio ya configurado
     * del mismo organization_type (pantallas organization-types/access y businesses/modules).
     */
    public function handle(Business $business): void
    {
        if ($business->organization_type_id === null) {
            return;
        }

        $this->syncRolesAndPermissions($business);
        $this->syncMenuModules($business);
    }

    private function syncRolesAndPermissions(Business $business): void
    {
        $template = Business::query()
            ->where('organization_type_id', $business->organization_type_id)
            ->whereKeyNot($business->id)
            ->where(function ($query) {
                $query->whereHas('roles')->orWhereHas('permissions');
            })
            ->with([
                'roles:id',
                'permissions:id,name',
            ])
            ->orderByRaw('CASE WHEN business_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('id')
            ->first();

        if ($template === null) {
            return;
        }

        BusinessAccess::syncBusinessAccess(
            $business,
            $template->roles->pluck('id')->map(fn ($id) => (int) $id)->all(),
            $template->permissions->pluck('name')->all()
        );
    }

    private function syncMenuModules(Business $business): void
    {
        if (! BusinessModuleAccess::canManageModules($business)) {
            return;
        }

        $template = Business::query()
            ->where('organization_type_id', $business->organization_type_id)
            ->whereKeyNot($business->id)
            ->whereNull('business_id')
            ->where(function ($query) {
                $query->whereHas('menuSections')->orWhereHas('menuItems');
            })
            ->with([
                'menuSections:id',
                'menuItems:id',
            ])
            ->orderBy('id')
            ->first();

        if ($template === null) {
            return;
        }

        BusinessModuleAccess::syncBusinessModules(
            $business,
            $template->menuSections->pluck('id')->map(fn ($id) => (int) $id)->all(),
            $template->menuItems->pluck('id')->map(fn ($id) => (int) $id)->all()
        );
    }
}
