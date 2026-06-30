<?php

use App\Http\Controllers\AuthController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Roles\Index as AdminRolesIndex;
use App\Livewire\Admin\User\Index as AdminUserIndex;
use App\Livewire\Admin\Subscriptions\Index as AdminSubscriptionsIndex;
use App\Livewire\Admin\Subscriptions\Plans\Index as AdminSubscriptionPlansIndex;
use App\Livewire\Admin\Payments\Index as AdminPaymentsIndex;
use App\Livewire\Admin\BankAccounts\Index as AdminBankAccountsIndex;
use App\Livewire\Admin\Banks\Index as AdminBanksIndex;
use App\Livewire\Admin\Finance\Index as AdminFinanceIndex;
use App\Livewire\Admin\Businesses\Index as AdminBusinessesIndex;
use App\Livewire\Admin\Businesses\Show as AdminBusinessesShow;
use App\Livewire\Comercio\Business\Edit as ComercioBusinessEdit;
use App\Livewire\Admin\Workshop\Clients\Form as WorkshopClientsForm;
use App\Livewire\Admin\Workshop\Clients\Index as WorkshopClientsIndex;
use App\Livewire\Admin\Workshop\Equipment\Form as WorkshopEquipmentForm;
use App\Livewire\Admin\Workshop\Equipment\Index as WorkshopEquipmentIndex;
use App\Livewire\Admin\Workshop\Equipment\Show as WorkshopEquipmentShow;
use App\Livewire\Admin\Workshop\Equipment\TypeIndex as WorkshopEquipmentTypeIndex;
use App\Livewire\Admin\Workshop\Quotations\Index as WorkshopQuotationsIndex;
use App\Livewire\Admin\Workshop\Quotations\Show as WorkshopQuotationsShow;
use App\Livewire\Admin\Workshop\WorkOrders\Index as WorkshopWorkOrdersIndex;
use App\Livewire\Admin\Workshop\WorkOrders\Show as WorkshopWorkOrdersShow;
use App\Livewire\Admin\Catalog\Services\Index as CatalogServicesIndex;
use App\Livewire\Admin\Catalog\SpareParts\Index as CatalogSparePartsIndex;
use App\Livewire\Admin\Settings\Equipment\Attributes\Form as SettingsAttributesForm;
use App\Livewire\Admin\Settings\Equipment\Attributes\Index as SettingsAttributesIndex;
use App\Livewire\Admin\Settings\Equipment\Attributes\Show as SettingsAttributesShow;
use App\Livewire\Admin\Settings\Equipment\Brands\Index as SettingsBrandsIndex;
use App\Livewire\Admin\Settings\Equipment\Brands\Show as SettingsBrandsShow;
use App\Livewire\Admin\Settings\Equipment\Models\Index as SettingsEquipmentModelsIndex;
use App\Livewire\Admin\Settings\Equipment\Models\Show as SettingsEquipmentModelsShow;
use App\Livewire\Admin\Settings\Equipment\Types\Index as SettingsEquipmentTypesIndex;
use App\Livewire\Admin\Settings\Equipment\Types\Show as SettingsEquipmentTypesShow;
use App\Livewire\Admin\Settings\Equipment\Index as SettingsEquipmentIndex;
use App\Livewire\Admin\Settings\Equipment\SectionIndex as SettingsEquipmentSectionIndex;
use App\Livewire\Auth\RegisterWizard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', RegisterWizard::class)->name('register');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/pending-activation', fn () => view('auth.pending-activation'))->name('pending-activation');

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Gestión de usuarios
    Route::middleware('permission:users.view')->group(function () {
        Route::get('/admin/users', AdminUserIndex::class)->name('admin.users.index');
    });

    // Gestión de roles (solo superAdmin)
    Route::middleware('role:superAdmin')->group(function () {
        Route::get('/admin/roles', AdminRolesIndex::class)->name('admin.roles.index');
    });

    // Gestión de suscripciones (solo superAdmin)
    Route::middleware('role:superAdmin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/subscriptions', AdminSubscriptionsIndex::class)->name('subscriptions.index');
        Route::get('/subscriptions/plans', AdminSubscriptionPlansIndex::class)->name('subscriptions.plans.index');
        Route::get('/payments', AdminPaymentsIndex::class)->name('payments.index');
        Route::get('/finanzas', AdminFinanceIndex::class)->name('finance.index');
        Route::get('/bank-accounts', AdminBankAccountsIndex::class)->name('bank-accounts.index');
        Route::get('/banks', AdminBanksIndex::class)->name('banks.index');
        Route::get('/businesses', AdminBusinessesIndex::class)->name('businesses.index');
        Route::get('/businesses/{business}', AdminBusinessesShow::class)->name('businesses.show');
    });

    // Rutas del Comercio
    Route::middleware('role:Comercio')->prefix('comercio')->name('comercio.')->group(function () {
        Route::get('/mi-negocio', ComercioBusinessEdit::class)->name('business.edit');
    });

    // Módulo Taller — Clientes
    Route::prefix('taller')->name('admin.workshop.')->group(function () {
        Route::middleware('permission:workshop.clients.view')->group(function () {
            Route::get('/clientes', WorkshopClientsIndex::class)->name('clients.index');
        });
        Route::middleware('permission:workshop.clients.create')->group(function () {
            Route::get('/clientes/form', WorkshopClientsForm::class)->name('clients.form');
        });
        Route::middleware('permission:workshop.clients.edit')->group(function () {
            Route::get('/clientes/{client}/form', WorkshopClientsForm::class)->name('clients.form.edit');
        });
        Route::middleware('permission:workshop.equipment.view')->group(function () {
            Route::get('/equipos', WorkshopEquipmentIndex::class)->name('equipment.index');
            Route::get('/equipos/{equipmentType}', WorkshopEquipmentTypeIndex::class)->name('equipment.type');
            Route::get('/equipos/{equipmentType}/{equipment}', WorkshopEquipmentShow::class)->name('equipment.show');
        });
        Route::middleware('permission:workshop.equipment.create')->group(function () {
            Route::get('/equipos/{equipmentType}/crear', WorkshopEquipmentForm::class)->name('equipment.form');
        });
        Route::middleware('permission:workshop.equipment.edit')->group(function () {
            Route::get('/equipos/{equipmentType}/{equipment}/editar', WorkshopEquipmentForm::class)->name('equipment.form.edit');
        });
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

    // Configuración
    Route::middleware('permission:settings.view')->prefix('configuracion')->name('admin.settings.')->group(function () {
        Route::get('/equipos', SettingsEquipmentIndex::class)->name('equipment.index');
        Route::middleware('permission:settings.equipment_types.view')->group(function () {
            Route::get('/equipos/tipos', SettingsEquipmentTypesIndex::class)->name('equipment.types');
            Route::get('/equipos/tipos/{equipmentType}', SettingsEquipmentTypesShow::class)->name('equipment.types.show');
        });
        Route::get('/equipos/marcas', SettingsBrandsIndex::class)->name('equipment.brands');
        Route::get('/equipos/marcas/{brand}', SettingsBrandsShow::class)->name('equipment.brands.show');
        Route::get('/equipos/modelos', SettingsEquipmentModelsIndex::class)->name('equipment.models');
        Route::get('/equipos/modelos/{equipmentModel}', SettingsEquipmentModelsShow::class)->name('equipment.models.show');
        Route::middleware('permission:settings.attributes.view')->group(function () {
            Route::get('/equipos/atributos', SettingsAttributesIndex::class)->name('equipment.attributes.index');
        });
        Route::middleware('permission:settings.attributes.create')->group(function () {
            Route::get('/equipos/atributos/crear', SettingsAttributesForm::class)->name('equipment.attributes.create');
        });
        Route::middleware('permission:settings.attributes.edit')->group(function () {
            Route::get('/equipos/atributos/{attribute}/editar', SettingsAttributesForm::class)->name('equipment.attributes.edit');
        });
        Route::middleware('permission:settings.attributes.view')->group(function () {
            Route::get('/equipos/atributos/{attribute}', SettingsAttributesShow::class)->name('equipment.attributes.show');
        });
        Route::get('/equipos/{section}', SettingsEquipmentSectionIndex::class)->name('equipment.section');
    });
});
