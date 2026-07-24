<?php

namespace App\Providers;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Business;
use App\Models\BusinessBankAccount;
use App\Models\BusinessPaymentMethod;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\EquipmentModel;
use App\Models\EquipmentType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\Quotation;
use App\Models\QuotationServiceType;
use App\Models\Remission;
use App\Models\TeamPosition;
use App\Models\Unit;
use App\Models\WorkOrder;
use App\Support\CurrentBusiness;
use App\Support\SidebarMenuBuilder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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
        // Evita Mixed Content: @vite/asset() deben ser https detrás del proxy.
        if (! $this->app->environment('local')) {
            URL::forceScheme('https');
        }

        Route::bind('client', fn (string $value) => Client::query()
            ->forAuthUser()
            ->whereKey($value)
            ->firstOrFail());

        Route::bind('business', fn (string $value) => Business::query()
            ->forAuthUser()
            ->whereKey($value)
            ->firstOrFail());

        Route::bind('equipment', fn (string $value) => Equipment::query()
            ->forAuthUser()
            ->whereKey($value)
            ->firstOrFail());

        Route::bind('brand', fn (string $value) => Brand::query()
            ->visibleToUser()
            ->whereKey($value)
            ->firstOrFail());

        Route::bind('equipmentModel', fn (string $value) => EquipmentModel::query()
            ->visibleToUser()
            ->whereKey($value)
            ->firstOrFail());

        Route::bind('attribute', fn (string $value) => Attribute::query()
            ->forAuthUser()
            ->whereKey($value)
            ->firstOrFail());

        Route::bind('quotation', fn (string $value) => Quotation::query()
            ->forAuthUser()
            ->whereKey($value)
            ->firstOrFail());

        Route::bind('quotationServiceType', fn (string $value) => QuotationServiceType::query()
            ->visibleToUser()
            ->whereKey($value)
            ->firstOrFail());

        Route::bind('workOrder', fn (string $value) => WorkOrder::query()
            ->forAuthUser()
            ->whereKey($value)
            ->firstOrFail());

        Route::bind('remission', fn (string $value) => Remission::query()
            ->forAuthUser()
            ->whereKey($value)
            ->firstOrFail());

        Route::bind('teamPosition', fn (string $value) => TeamPosition::query()
            ->visibleToUser()
            ->whereKey($value)
            ->firstOrFail());

        Route::bind('productType', fn (string $value) => ProductType::query()
            ->visibleToUser()
            ->whereKey($value)
            ->firstOrFail());

        Route::bind('productCategory', fn (string $value) => ProductCategory::query()
            ->visibleToUser()
            ->whereKey($value)
            ->firstOrFail());

        Route::bind('unit', fn (string $value) => Unit::query()
            ->visibleToUser()
            ->whereKey($value)
            ->firstOrFail());

        Route::bind('product', fn (string $value) => Product::query()
            ->forAuthUser()
            ->whereKey($value)
            ->firstOrFail());

        Route::bind('paymentMethod', fn (string $value) => BusinessPaymentMethod::query()
            ->visibleToUser()
            ->whereKey($value)
            ->firstOrFail());

        Route::bind('bankAccount', fn (string $value) => BusinessBankAccount::query()
            ->forAuthUser()
            ->whereKey($value)
            ->firstOrFail());

        Route::bind('equipmentType', function (string $value) {
            $equipment_type = EquipmentType::query()->whereKey($value)->firstOrFail();

            if (request()->routeIs([
                'admin.workshop.equipment.type',
                'admin.workshop.equipment.show',
                'admin.workshop.equipment.form',
                'admin.workshop.equipment.form.edit',
                'admin.settings.equipment.types.show',
            ]) && ! $equipment_type->isAccessibleToUser()) {
                abort(404);
            }

            return $equipment_type;
        });

        View::composer('layouts.app', function ($view) {
            $user = auth()->user();

            if ($user) {
                $user->loadMissing('businesses');
            }

            $builder = app(SidebarMenuBuilder::class);

            $view->with([
                'sidebarMenuSections'  => $builder->build($user),
                'sidebarActiveSlugs'   => $builder->activeSectionSlugs($user),
                'currentBusiness'      => $user ? (CurrentBusiness::get() ?? $user->primaryBusiness()) : null,
                'selectableBusinesses' => $user ? CurrentBusiness::selectableBusinessesFor($user) : collect(),
            ]);
        });
    }
}
