<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Asocia al superAdmin con el negocio dueño de la plataforma.
 *
 * Hasta ahora el superAdmin no pertenecía a ningún negocio: le bastaba porque
 * los alcances lo dejan pasar por su rol. Pero desde que VISERVICE le factura a
 * sus suscriptores necesita un negocio propio del cual colgar el catálogo de
 * planes y la configuración DIAN del emisor.
 *
 * De paso resuelve un efecto secundario: varias pantallas resuelven el negocio
 * con «auth()->user()->business_id», que para el superAdmin venía nulo.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_business')) {
            return;
        }

        $business_id = (int) config('subscriptions.owner_business_id');

        if (! DB::table('businesses')->where('id', $business_id)->exists()) {
            return;
        }

        $super_admin_ids = DB::table('users')
            ->join('model_has_roles', function ($join) {
                $join->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', '=', \App\Models\User::class);
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'superAdmin')
            ->pluck('users.id');

        foreach ($super_admin_ids as $user_id) {
            $exists = DB::table('user_business')
                ->where('user_id', $user_id)
                ->where('business_id', $business_id)
                ->exists();

            if ($exists) {
                continue;
            }

            // Primario solo si el usuario no tenía ya otro marcado.
            $has_primary = DB::table('user_business')
                ->where('user_id', $user_id)
                ->where('is_primary', true)
                ->exists();

            DB::table('user_business')->insert([
                'user_id'     => $user_id,
                'business_id' => $business_id,
                'is_primary'  => ! $has_primary,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        // No se revierte: dejar al superAdmin sin negocio rompería el catálogo de
        // planes, y volver a asociarlo es trivial desde el seeder.
    }
};
