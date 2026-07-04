<?php

namespace App\Livewire\Concerns;

trait ResolvesCatalogDatatableRowPermissions
{
    /**
     * Calcula permisos de fila sin consultas adicionales (evita N+1 en columnas de acciones).
     *
     * @return array{can_edit: bool, can_delete: bool, is_general_readonly: bool}
     */
    protected function catalogRowPermissions(
        bool $general,
        mixed $business_id,
        mixed $equipment_count = 0,
        string $edit_permission = 'settings.edit',
        string $delete_permission = 'settings.edit',
    ): array {
        $business_id     = $business_id !== null && $business_id !== '' ? (int) $business_id : null;
        $equipment_count = (int) $equipment_count;
        $user            = auth()->user();

        if (! $user?->can($edit_permission) && ! $user?->can($delete_permission)) {
            return [
                'can_edit'            => false,
                'can_delete'          => false,
                'is_general_readonly' => $general && ! $user?->hasRole('superAdmin'),
            ];
        }

        if ($user->hasRole('superAdmin')) {
            return [
                'can_edit'            => $user->can($edit_permission),
                'can_delete'          => $user->can($delete_permission) && $equipment_count === 0,
                'is_general_readonly' => false,
            ];
        }

        $is_owner = ! $general && $business_id !== null && $user->belongsToBusiness($business_id);

        return [
            'can_edit'            => $is_owner && $user->can($edit_permission),
            'can_delete'          => $is_owner && $user->can($delete_permission) && $equipment_count === 0,
            'is_general_readonly' => $general,
        ];
    }
}
