<?php

namespace App\Livewire\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait JoinsEquipmentUsageCount
{
    /**
     * Agrega conteo de equipos vía subquery join (compatible con Livewire Datatables).
     *
     * El paquete arm092/livewire-datatables prefija columnas sin tabla en callbacks
     * (ej. equipment_count → equipment_types.equipment_count). Usar siempre el alias
     * calificado `equipment_usage.equipment_count` en Column::callback.
     */
    protected function joinEquipmentUsageCount(Builder $query, string $catalog_table, string $equipment_foreign_key): Builder
    {
        $subquery = DB::table('equipment')
            ->select($equipment_foreign_key, DB::raw('COUNT(*) as equipment_count'))
            ->whereNull('deleted_at');

        $user = auth()->user();

        if ($user && ! $user->hasRole('superAdmin')) {
            $business_ids = $user->businessIds();

            if ($business_ids === []) {
                $subquery->whereRaw('0 = 1');
            } else {
                $subquery->whereIn('business_id', $business_ids);
            }
        }

        $subquery->groupBy($equipment_foreign_key);

        return $query->leftJoinSub(
            $subquery,
            'equipment_usage',
            fn ($join) => $join->on("{$catalog_table}.id", '=', "equipment_usage.{$equipment_foreign_key}")
        );
    }
}
