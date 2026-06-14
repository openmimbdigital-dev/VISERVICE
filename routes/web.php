<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Roles\Index as AdminRolesIndex;
use App\Livewire\Admin\User\Index as AdminUserIndex;
use App\Livewire\Admin\Subscriptions\Index as AdminSubscriptionsIndex;
use App\Livewire\Admin\Subscriptions\Plans\Index as AdminSubscriptionPlansIndex;
use App\Livewire\Admin\Workshop\Clients\Index as WorkshopClientsIndex;
use App\Livewire\Admin\Workshop\Vehicles\Index as WorkshopVehiclesIndex;
use App\Livewire\Admin\Workshop\Quotations\Index as WorkshopQuotationsIndex;
use App\Livewire\Admin\Workshop\Quotations\Show as WorkshopQuotationsShow;
use App\Livewire\Admin\Workshop\WorkOrders\Index as WorkshopWorkOrdersIndex;
use App\Livewire\Admin\Workshop\WorkOrders\Show as WorkshopWorkOrdersShow;
use App\Livewire\Admin\Catalog\Services\Index as CatalogServicesIndex;
use App\Livewire\Admin\Catalog\SpareParts\Index as CatalogSparePartsIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Gestión de usuarios
    Route::middleware('permission:users.view')->group(function () {
        Route::get('/admin/users', AdminUserIndex::class)->name('admin.users.index');
    });

    // Gestión de roles
    Route::middleware('permission:roles.view')->group(function () {
        Route::get('/admin/roles', AdminRolesIndex::class)->name('admin.roles.index');
    });

    // Gestión de suscripciones (solo superAdmin)
    Route::middleware('role:superAdmin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/subscriptions', AdminSubscriptionsIndex::class)->name('subscriptions.index');
        Route::get('/subscriptions/plans', AdminSubscriptionPlansIndex::class)->name('subscriptions.plans.index');
    });

    // Módulo Taller
    Route::prefix('taller')->name('admin.workshop.')->group(function () {
        Route::get('/clientes', WorkshopClientsIndex::class)->name('clients.index');
        Route::get('/vehiculos', WorkshopVehiclesIndex::class)->name('vehicles.index');
        Route::get('/cotizaciones', WorkshopQuotationsIndex::class)->name('quotations.index');
        Route::get('/cotizaciones/{quotation}', WorkshopQuotationsShow::class)->name('quotations.show');
        Route::get('/ordenes', WorkshopWorkOrdersIndex::class)->name('work-orders.index');
        Route::get('/ordenes/{workOrder}', WorkshopWorkOrdersShow::class)->name('work-orders.show');
    });

    // Catálogo
    Route::prefix('catalogo')->name('admin.workshop.catalog.')->group(function () {
        Route::get('/servicios', CatalogServicesIndex::class)->name('services.index');
        Route::get('/repuestos', CatalogSparePartsIndex::class)->name('spare-parts.index');
    });
});
