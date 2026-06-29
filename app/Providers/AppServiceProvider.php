<?php

namespace App\Providers;

use App\Models\EquipmentType;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('equipmentType', function (string $value) {
            $equipment_type = EquipmentType::query()->whereKey($value)->firstOrFail();

            if (request()->routeIs('admin.workshop.equipment.type')
                && ! $equipment_type->isAccessibleToUser()) {
                abort(404);
            }

            return $equipment_type;
        });
    }
}
