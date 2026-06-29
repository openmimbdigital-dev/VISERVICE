<?php

namespace App\Livewire\Concerns;

trait ResolvesCatalogDatatableRowPermissions
{
    /**
     * Calcula permisos de fila sin consultas adicionales (evita N+1 en columnas de acciones).
     *
     * @return array{can_edit: bool, can_delete: bool, is_general_readonly: bool}
     */
    protected function catalogRowPermissions(bool $general, mixed $business_id, mixed $equipment_count = 0): array
    {
        $business_id     = $business_id !== null && $business_id !== '' ? (int) $business_id : null;
        $equipment_count = (int) $equipment_count;
        $user            = auth()->user();

        if (! $user?->can('settings.edit')) {
            return [
                'can_edit'            => false,
                'can_delete'          => false,
                'is_general_readonly' => $general && ! $user?->hasRole('superAdmin'),
            ];
        }

        if ($user->hasRole('superAdmin')) {
            return [
                'can_edit'            => true,
                'can_delete'          => $equipment_count === 0,
                'is_general_readonly' => false,
            ];
        }

        $is_owner = ! $general && $business_id === $user->business_id;

        return [
            'can_edit'            => $is_owner,
            'can_delete'          => $is_owner && $equipment_count === 0,
            'is_general_readonly' => $general,
        ];
    }
}
