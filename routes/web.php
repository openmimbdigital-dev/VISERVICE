<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CurrentBusinessController;
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
use App\Livewire\Admin\Businesses\Form as AdminBusinessesForm;
use App\Livewire\Admin\Businesses\ModuleAccess as AdminBusinessesModuleAccess;
use App\Livewire\Admin\Businesses\Show as AdminBusinessesShow;
use App\Livewire\Admin\BusinessTypes\Access as AdminBusinessTypesAccess;
use App\Livewire\Admin\BusinessTypes\Index as AdminBusinessTypesIndex;
use App\Livewire\Admin\OrganizationTypes\Index as AdminOrganizationTypesIndex;
use App\Livewire\Admin\TeamPositions\Index as AdminTeamPositionsIndex;
use App\Livewire\Admin\TeamPositions\Show as AdminTeamPositionsShow;
use App\Livewire\Admin\Businesses\PaymentMethods\Index as AdminBusinessPaymentMethodsIndex;
use App\Livewire\Admin\Businesses\PaymentMethods\Show as AdminBusinessPaymentMethodsShow;
use App\Livewire\Admin\Businesses\BankAccounts\Index as AdminBusinessBankAccountsIndex;
use App\Livewire\Admin\Businesses\BankAccounts\Show as AdminBusinessBankAccountsShow;
use App\Livewire\Comercio\Business\Edit as ComercioBusinessEdit;
use App\Livewire\Admin\Workshop\Clients\Form as WorkshopClientsForm;
use App\Livewire\Admin\Workshop\Clients\Index as WorkshopClientsIndex;
use App\Livewire\Admin\Workshop\Equipment\Form as WorkshopEquipmentForm;
use App\Livewire\Admin\Workshop\Equipment\Index as WorkshopEquipmentIndex;
use App\Livewire\Admin\Workshop\Equipment\Show as WorkshopEquipmentShow;
use App\Livewire\Admin\Workshop\Equipment\TypeIndex as WorkshopEquipmentTypeIndex;
use App\Livewire\Admin\Workshop\Quotations\Form as WorkshopQuotationsForm;
use App\Livewire\Admin\Workshop\Quotations\Index as WorkshopQuotationsIndex;
use App\Livewire\Admin\Workshop\Quotations\PrintView as WorkshopQuotationsPrint;
use App\Livewire\Admin\Workshop\Quotations\Show as WorkshopQuotationsShow;
use App\Livewire\Admin\Workshop\QuotationServiceTypes\Index as WorkshopQuotationServiceTypesIndex;
use App\Livewire\Admin\Workshop\QuotationServiceTypes\Show as WorkshopQuotationServiceTypesShow;
use App\Livewire\Admin\Workshop\WorkOrders\Index as WorkshopWorkOrdersIndex;
use App\Livewire\Admin\Workshop\WorkOrders\Show as WorkshopWorkOrdersShow;
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
use App\Livewire\Admin\Settings\Catalog\Index as SettingsCatalogProductsIndex;
use App\Livewire\Admin\Settings\Catalog\ItemTypes\Index as SettingsItemTypesIndex;
use App\Livewire\Admin\Settings\Catalog\ItemTypes\Show as SettingsItemTypesShow;
use App\Livewire\Admin\Settings\Catalog\ItemCategories\Index as SettingsItemCategoriesIndex;
use App\Livewire\Admin\Settings\Catalog\ItemCategories\Show as SettingsItemCategoriesShow;
use App\Livewire\Admin\Settings\Catalog\Units\Index as SettingsUnitsIndex;
use App\Livewire\Admin\Settings\Catalog\Units\Show as SettingsUnitsShow;
use App\Livewire\Admin\Settings\Catalog\Brands\Form as SettingsCatalogBrandsForm;
use App\Livewire\Admin\Settings\Catalog\Brands\Index as SettingsCatalogBrandsIndex;
use App\Livewire\Admin\Settings\Catalog\Brands\Show as SettingsCatalogBrandsShow;
use App\Livewire\Admin\Catalog\Items\Form as CatalogItemsForm;
use App\Livewire\Admin\Catalog\Items\Index as CatalogItemsIndex;
use App\Livewire\Admin\Catalog\Items\Show as CatalogItemsShow;
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

Route::middleware(['auth', 'ensure.business', 'business.module'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/current-business', [CurrentBusinessController::class, 'switch'])->name('current-business.switch');

    Route::get('/pending-activation', fn () => view('auth.pending-activation'))->name('pending-activation');

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
        Route::get('/payments', AdminPaymentsIndex::class)->name('payments.index');
        Route::get('/finanzas', AdminFinanceIndex::class)->name('finance.index');
        Route::get('/bank-accounts', AdminBankAccountsIndex::class)->name('bank-accounts.index');
        Route::get('/banks', AdminBanksIndex::class)->name('banks.index');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::middleware('permission:businesses.view')->group(function () {
            Route::get('/businesses', AdminBusinessesIndex::class)->name('businesses.index');
        });
        Route::middleware('permission:businesses.create')->group(function () {
            Route::get('/businesses/form', AdminBusinessesForm::class)->name('businesses.form');
        });
        Route::middleware('role:superAdmin')->group(function () {
            Route::get('/businesses/modules', AdminBusinessesModuleAccess::class)->name('businesses.modules');
        });
        Route::middleware('permission:businesses.edit')->group(function () {
            Route::get('/businesses/{business}/form', AdminBusinessesForm::class)
                ->whereNumber('business')
                ->name('businesses.form.edit');
        });
        Route::middleware('permission:businesses.view')->group(function () {
            Route::get('/businesses/{business}', AdminBusinessesShow::class)
                ->whereNumber('business')
                ->name('businesses.show');
        });
        Route::middleware('permission:business_types.view')->group(function () {
            Route::get('/business-types', AdminBusinessTypesIndex::class)->name('business-types.index');
        });
        Route::middleware('permission:business_types.access.view')->group(function () {
            Route::get('/business-types/access', AdminBusinessTypesAccess::class)->name('business-types.access');
        });
        Route::middleware('permission:organization_types.view')->group(function () {
            Route::get('/organization-types', AdminOrganizationTypesIndex::class)->name('organization-types.index');
        });
        Route::middleware('permission:team_positions.view')->group(function () {
            Route::get('/team-positions', AdminTeamPositionsIndex::class)->name('team-positions.index');
            Route::get('/team-positions/{teamPosition}', AdminTeamPositionsShow::class)
                ->whereNumber('teamPosition')
                ->name('team-positions.show');
        });
        Route::middleware('permission:business_payment_methods.view')->group(function () {
            Route::get('/metodos-pago', AdminBusinessPaymentMethodsIndex::class)->name('business-payment-methods.index');
            Route::get('/metodos-pago/{paymentMethod}', AdminBusinessPaymentMethodsShow::class)
                ->whereNumber('paymentMethod')
                ->name('business-payment-methods.show');
        });
        Route::middleware('permission:business_bank_accounts.view')->group(function () {
            Route::get('/datos-bancarios', AdminBusinessBankAccountsIndex::class)->name('business-bank-accounts.index');
            Route::get('/datos-bancarios/{bankAccount}', AdminBusinessBankAccountsShow::class)
                ->whereNumber('bankAccount')
                ->name('business-bank-accounts.show');
        });
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
        Route::middleware('permission:workshop.quotations.view')->group(function () {
            Route::get('/cotizaciones', WorkshopQuotationsIndex::class)->name('quotations.index');
        });
        Route::middleware('permission:workshop.quotations.create')->group(function () {
            Route::get('/cotizaciones/form', WorkshopQuotationsForm::class)->name('quotations.form');
        });
        Route::middleware('permission:workshop.quotations.edit')->group(function () {
            Route::get('/cotizaciones/{quotation}/form', WorkshopQuotationsForm::class)->name('quotations.form.edit');
        });
        Route::middleware('permission:workshop.quotations.view')->group(function () {
            Route::get('/cotizaciones/{quotation}/imprimir', WorkshopQuotationsPrint::class)->name('quotations.print');
            Route::get('/cotizaciones/{quotation}', WorkshopQuotationsShow::class)->name('quotations.show');
        });
        Route::middleware('permission:workshop.quotation_service_types.view')->group(function () {
            Route::get('/tipos-servicio', WorkshopQuotationServiceTypesIndex::class)->name('quotation-service-types.index');
            Route::get('/tipos-servicio/{quotationServiceType}', WorkshopQuotationServiceTypesShow::class)->name('quotation-service-types.show');
        });
        Route::middleware('permission:workshop.work-orders.view')->group(function () {
            Route::get('/ordenes', WorkshopWorkOrdersIndex::class)->name('work-orders.index');
            Route::get('/ordenes/{workOrder}', WorkshopWorkOrdersShow::class)->name('work-orders.show');
        });
    });

    // Configuración
    Route::middleware('permission:settings.view')->prefix('configuracion')->name('admin.settings.')->group(function () {
        Route::get('/equipos', SettingsEquipmentIndex::class)->name('equipment.index');
        Route::middleware('permission:settings.equipment_types.view')->group(function () {
            Route::get('/equipos/tipos', SettingsEquipmentTypesIndex::class)->name('equipment.types');
            Route::get('/equipos/tipos/{equipmentType}', SettingsEquipmentTypesShow::class)->name('equipment.types.show');
        });
        Route::middleware('permission:settings.brands.view')->group(function () {
            Route::get('/equipos/marcas', SettingsBrandsIndex::class)->name('equipment.brands');
            Route::get('/equipos/marcas/{brand}', SettingsBrandsShow::class)->name('equipment.brands.show');
        });
        Route::middleware('permission:settings.model_equipment.view')->group(function () {
            Route::get('/equipos/modelos', SettingsEquipmentModelsIndex::class)->name('equipment.models');
            Route::get('/equipos/modelos/{equipmentModel}', SettingsEquipmentModelsShow::class)->name('equipment.models.show');
        });
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

        // Configuración — Productos y servicios (directorios)
        Route::get('/productos-servicios', SettingsCatalogProductsIndex::class)->name('catalog-products.index');

        Route::middleware('permission:settings.item_types.view')->group(function () {
            Route::get('/productos-servicios/tipos', SettingsItemTypesIndex::class)->name('catalog-products.item-types.index');
            Route::get('/productos-servicios/tipos/{itemType}', SettingsItemTypesShow::class)->name('catalog-products.item-types.show');
        });

        Route::middleware('permission:settings.item_categories.view')->group(function () {
            Route::get('/productos-servicios/categorias', SettingsItemCategoriesIndex::class)->name('catalog-products.item-categories.index');
            Route::get('/productos-servicios/categorias/{itemCategory}', SettingsItemCategoriesShow::class)->name('catalog-products.item-categories.show');
        });

        Route::middleware('permission:settings.units.view')->group(function () {
            Route::get('/productos-servicios/unidades', SettingsUnitsIndex::class)->name('catalog-products.units.index');
            Route::get('/productos-servicios/unidades/{unit}', SettingsUnitsShow::class)->name('catalog-products.units.show');
        });

        Route::middleware('permission:settings.brands.view')->group(function () {
            Route::get('/productos-servicios/marcas', SettingsCatalogBrandsIndex::class)->name('catalog-products.brands.index');
        });
        Route::middleware('permission:settings.brands.create')->group(function () {
            Route::get('/productos-servicios/marcas/crear', SettingsCatalogBrandsForm::class)->name('catalog-products.brands.create');
        });
        Route::middleware('permission:settings.brands.edit')->group(function () {
            Route::get('/productos-servicios/marcas/{brand}/editar', SettingsCatalogBrandsForm::class)->name('catalog-products.brands.edit');
        });
        Route::middleware('permission:settings.brands.view')->group(function () {
            Route::get('/productos-servicios/marcas/{brand}', SettingsCatalogBrandsShow::class)->name('catalog-products.brands.show');
        });
    });

    // Catálogo — Productos y servicios
    Route::middleware('permission:catalog.view')->prefix('catalogo')->name('admin.catalog.')->group(function () {
        Route::middleware('permission:catalog.items.view')->group(function () {
            Route::get('/productos', CatalogItemsIndex::class)->name('items.index');
        });
        Route::middleware('permission:catalog.items.create')->group(function () {
            Route::get('/productos/crear', CatalogItemsForm::class)->name('items.create');
        });
        Route::middleware('permission:catalog.items.edit')->group(function () {
            Route::get('/productos/{item}/editar', CatalogItemsForm::class)->name('items.edit');
        });
        Route::middleware('permission:catalog.items.view')->group(function () {
            Route::get('/productos/{item}', CatalogItemsShow::class)->name('items.show');
        });
    });
});
