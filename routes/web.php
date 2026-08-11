<?php

use App\Http\Controllers\Admin\Events\ScheduleEventsFeedController;
use App\Http\Controllers\Admin\Reports\Events\EventAttendancePdfController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CurrentBusinessController;
use App\Http\Controllers\Workshop\WorkshopPdfController;
use App\Livewire\Admin\BankAccounts\Index as AdminBankAccountsIndex;
use App\Livewire\Admin\Banks\Index as AdminBanksIndex;
use App\Livewire\Admin\Businesses\BankAccounts\Index as AdminBusinessBankAccountsIndex;
use App\Livewire\Admin\Businesses\BankAccounts\Show as AdminBusinessBankAccountsShow;
use App\Livewire\Admin\Businesses\Form as AdminBusinessesForm;
use App\Livewire\Admin\Businesses\Index as AdminBusinessesIndex;
use App\Livewire\Admin\Businesses\ModuleAccess as AdminBusinessesModuleAccess;
use App\Livewire\Admin\Businesses\PaymentMethods\Index as AdminBusinessPaymentMethodsIndex;
use App\Livewire\Admin\Businesses\PaymentMethods\Show as AdminBusinessPaymentMethodsShow;
use App\Livewire\Admin\Businesses\Show as AdminBusinessesShow;
use App\Livewire\Admin\BusinessTypes\Index as AdminBusinessTypesIndex;
use App\Livewire\Admin\Catalog\Products\Form as CatalogProductsForm;
use App\Livewire\Admin\Catalog\Products\Index as CatalogProductsIndex;
use App\Livewire\Admin\Catalog\Products\Show as CatalogProductsShow;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Events\Index as EventsIndex;
use App\Livewire\Admin\Events\Manage\CategoryIndex as EventsManageCategoryIndex;
use App\Livewire\Admin\Events\Manage\Form as EventsManageForm;
use App\Livewire\Admin\Events\Manage\Index as EventsManageIndex;
use App\Livewire\Admin\Events\Manage\Show as EventsManageShow;
use App\Livewire\Admin\Events\Schedule\Index as EventsScheduleIndex;
use App\Livewire\Admin\Events\Schedule\Show as EventsScheduleShow;
use App\Livewire\Admin\Events\TeamRoles\Form as EventsTeamRolesForm;
use App\Livewire\Admin\Events\TeamRoles\Index as EventsTeamRolesIndex;
use App\Livewire\Admin\Events\TeamRoles\Show as EventsTeamRolesShow;
use App\Livewire\Admin\Events\Teams\Form as EventsTeamsForm;
use App\Livewire\Admin\Events\Teams\Index as EventsTeamsIndex;
use App\Livewire\Admin\Events\Teams\Show as EventsTeamsShow;
use App\Livewire\Admin\Finance\Index as AdminFinanceIndex;
use App\Livewire\Admin\OrganizationTypes\Access as AdminOrganizationTypesAccess;
use App\Livewire\Admin\OrganizationTypes\Index as AdminOrganizationTypesIndex;
use App\Livewire\Admin\Payments\Index as AdminPaymentsIndex;
use App\Livewire\Admin\PublicParticipants\Access as AdminPublicParticipantsAccess;
use App\Livewire\Admin\Reports\Events\Attendance\CategoryIndex as ReportsEventsAttendanceCategoryIndex;
use App\Livewire\Admin\Reports\Events\Attendance\Index as ReportsEventsAttendanceIndex;
use App\Livewire\Admin\Reports\Events\Attendance\Show as ReportsEventsAttendanceShow;
use App\Livewire\Admin\Roles\Index as AdminRolesIndex;
use App\Livewire\Admin\Settings\Catalog\Brands\Form as SettingsCatalogBrandsForm;
use App\Livewire\Admin\Settings\Catalog\Brands\Index as SettingsCatalogBrandsIndex;
use App\Livewire\Admin\Settings\Catalog\Brands\Show as SettingsCatalogBrandsShow;
use App\Livewire\Admin\Settings\Catalog\Index as SettingsCatalogProductsIndex;
use App\Livewire\Admin\Settings\Catalog\ProductCategories\Index as SettingsProductCategoriesIndex;
use App\Livewire\Admin\Settings\Catalog\ProductCategories\Show as SettingsProductCategoriesShow;
use App\Livewire\Admin\Settings\Catalog\ProductTypes\Index as SettingsProductTypesIndex;
use App\Livewire\Admin\Settings\Catalog\ProductTypes\Show as SettingsProductTypesShow;
use App\Livewire\Admin\Settings\Catalog\Units\Index as SettingsUnitsIndex;
use App\Livewire\Admin\Settings\Catalog\Units\Show as SettingsUnitsShow;
use App\Livewire\Admin\Settings\Equipment\Attributes\Form as SettingsAttributesForm;
use App\Livewire\Admin\Settings\Equipment\Attributes\Index as SettingsAttributesIndex;
use App\Livewire\Admin\Settings\Equipment\Attributes\Show as SettingsAttributesShow;
use App\Livewire\Admin\Settings\Equipment\Brands\Index as SettingsBrandsIndex;
use App\Livewire\Admin\Settings\Equipment\Brands\Show as SettingsBrandsShow;
use App\Livewire\Admin\Settings\Equipment\Index as SettingsEquipmentIndex;
use App\Livewire\Admin\Settings\Equipment\Models\Index as SettingsEquipmentModelsIndex;
use App\Livewire\Admin\Settings\Equipment\Models\Show as SettingsEquipmentModelsShow;
use App\Livewire\Admin\Settings\Equipment\SectionIndex as SettingsEquipmentSectionIndex;
use App\Livewire\Admin\Settings\Equipment\Types\Index as SettingsEquipmentTypesIndex;
use App\Livewire\Admin\Settings\Equipment\Types\Show as SettingsEquipmentTypesShow;
use App\Livewire\Admin\Settings\Events\AttendeeTypes\Form as SettingsAttendeeTypesForm;
use App\Livewire\Admin\Settings\Events\AttendeeTypes\Index as SettingsAttendeeTypesIndex;
use App\Livewire\Admin\Settings\Events\AttendeeTypes\Show as SettingsAttendeeTypesShow;
use App\Livewire\Admin\Settings\Events\EventCategories\Form as SettingsEventCategoriesForm;
use App\Livewire\Admin\Settings\Events\EventCategories\Index as SettingsEventCategoriesIndex;
use App\Livewire\Admin\Settings\Events\EventCategories\Show as SettingsEventCategoriesShow;
use App\Livewire\Admin\Settings\Events\Index as SettingsEventsIndex;
use App\Livewire\Admin\Settings\General\Index as SettingsGeneralIndex;
use App\Livewire\Admin\Settings\General\Statuses\Index as SettingsStatusesIndex;
use App\Livewire\Admin\Subscriptions\Index as AdminSubscriptionsIndex;
use App\Livewire\Admin\Subscriptions\Plans\Index as AdminSubscriptionPlansIndex;
use App\Livewire\Admin\TeamPositions\Index as AdminTeamPositionsIndex;
use App\Livewire\Admin\TeamPositions\Show as AdminTeamPositionsShow;
use App\Livewire\Admin\Participants\Form as AdminParticipantsForm;
use App\Livewire\Admin\Participants\Index as AdminParticipantsIndex;
use App\Livewire\Admin\Participants\PublicRegistrationLink as AdminParticipantsPublicRegistrationLink;
use App\Livewire\Admin\Participants\Roles\Form as AdminParticipantRolesForm;
use App\Livewire\Admin\Participants\Roles\Index as AdminParticipantRolesIndex;
use App\Livewire\Admin\Participants\Roles\Show as AdminParticipantRolesShow;
use App\Livewire\Admin\Participants\Show as AdminParticipantsShow;
use App\Livewire\Admin\User\Index as AdminUserIndex;
use App\Livewire\Admin\Workshop\AdvancePayments\Index as WorkshopAdvancePaymentsIndex;
use App\Livewire\Admin\Workshop\AdvancePayments\Show as WorkshopAdvancePaymentsShow;
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
use App\Livewire\Admin\Workshop\Remissions\Form as WorkshopRemissionsForm;
use App\Livewire\Admin\Workshop\Remissions\Index as WorkshopRemissionsIndex;
use App\Livewire\Admin\Workshop\Remissions\PrintView as WorkshopRemissionsPrint;
use App\Livewire\Admin\Workshop\Remissions\Show as WorkshopRemissionsShow;
use App\Livewire\Admin\Workshop\WorkOrders\AssociatedDocuments\Index as WorkshopAssociatedDocumentsIndex;
use App\Livewire\Admin\Workshop\WorkOrders\Form as WorkshopWorkOrdersForm;
use App\Livewire\Admin\Workshop\WorkOrders\Index as WorkshopWorkOrdersIndex;
use App\Livewire\Admin\Workshop\WorkOrders\PrintView as WorkshopWorkOrdersPrint;
use App\Livewire\Admin\Workshop\WorkOrders\Show as WorkshopWorkOrdersShow;
use App\Livewire\Auth\RegisterWizard;
use App\Livewire\Comercio\Business\Edit as ComercioBusinessEdit;
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

    Route::middleware('active.subscription')->group(function () {
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
        Route::get('/finance', AdminFinanceIndex::class)->name('finance.index');
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

        // Gestión de Negocios (solo superAdmin)
        Route::middleware('role:superAdmin')->group(function () {
            Route::middleware('permission:organization_types.view')->group(function () {
                Route::get('/organization-types', AdminOrganizationTypesIndex::class)->name('organization-types.index');
            });
            Route::middleware('permission:organization_types.access.view')->group(function () {
                Route::get('/organization-types/access', AdminOrganizationTypesAccess::class)->name('organization-types.access');
            });
            Route::middleware('permission:business_types.view')->group(function () {
                Route::get('/business-types', AdminBusinessTypesIndex::class)->name('business-types.index');
            });
            Route::middleware('permission:businesses.manage_modules')->group(function () {
                Route::get('/businesses/modules', AdminBusinessesModuleAccess::class)->name('businesses.modules');
            });
            Route::middleware('permission:public_routes.manage')->group(function () {
                Route::get('/public-participants/access', AdminPublicParticipantsAccess::class)
                    ->name('public-participants.access');
            });
        });
        Route::middleware('permission:team_positions.view')->group(function () {
            Route::get('/team-positions', AdminTeamPositionsIndex::class)->name('team-positions.index');
            Route::get('/team-positions/{teamPosition}', AdminTeamPositionsShow::class)
                ->whereNumber('teamPosition')
                ->name('team-positions.show');
        });
        Route::middleware('permission:participants.create')->group(function () {
            Route::get('/participants/form', AdminParticipantsForm::class)->name('participants.form');
        });
        Route::middleware('permission:participants.roles.create')->group(function () {
            Route::get('/participants/roles/create', AdminParticipantRolesForm::class)->name('participants.roles.create');
        });
        Route::middleware('permission:participants.roles.edit')->group(function () {
            Route::get('/participants/roles/{participantRole}/edit', AdminParticipantRolesForm::class)
                ->whereNumber('participantRole')
                ->name('participants.roles.edit');
        });
        Route::middleware('permission:participants.roles.view')->group(function () {
            Route::get('/participants/roles', AdminParticipantRolesIndex::class)->name('participants.roles.index');
            Route::get('/participants/roles/{participantRole}', AdminParticipantRolesShow::class)
                ->whereNumber('participantRole')
                ->name('participants.roles.show');
        });
        Route::middleware('permission:participants.view')->group(function () {
            Route::get('/participants', AdminParticipantsIndex::class)->name('participants.index');
            Route::get('/participants/public-registration-link', AdminParticipantsPublicRegistrationLink::class)
                ->name('participants.public-registration-link');
            Route::get('/participants/{participant}', AdminParticipantsShow::class)
                ->whereNumber('participant')
                ->name('participants.show');
        });
        Route::middleware('permission:participants.edit')->group(function () {
            Route::get('/participants/{participant}/form', AdminParticipantsForm::class)
                ->whereNumber('participant')
                ->name('participants.form.edit');
        });
        Route::middleware('permission:business_payment_methods.view')->group(function () {
            Route::get('/payment-methods', AdminBusinessPaymentMethodsIndex::class)->name('business-payment-methods.index');
            Route::get('/payment-methods/{paymentMethod}', AdminBusinessPaymentMethodsShow::class)
                ->whereNumber('paymentMethod')
                ->name('business-payment-methods.show');
        });
        Route::middleware('permission:business_bank_accounts.view')->group(function () {
            Route::get('/business-bank-accounts', AdminBusinessBankAccountsIndex::class)->name('business-bank-accounts.index');
            Route::get('/business-bank-accounts/{bankAccount}', AdminBusinessBankAccountsShow::class)
                ->whereNumber('bankAccount')
                ->name('business-bank-accounts.show');
        });
    });

    // Rutas del Comercio
    Route::middleware('role:Comercio')->prefix('merchant')->name('merchant.')->group(function () {
        Route::get('/my-business', ComercioBusinessEdit::class)->name('business.edit');
    });

    // Módulo Taller — Clientes
    Route::prefix('workshop')->name('admin.workshop.')->group(function () {
        Route::middleware('permission:workshop.clients.view')->group(function () {
            Route::get('/clients', WorkshopClientsIndex::class)->name('clients.index');
        });
        Route::middleware('permission:workshop.clients.create')->group(function () {
            Route::get('/clients/form', WorkshopClientsForm::class)->name('clients.form');
        });
        Route::middleware('permission:workshop.clients.edit')->group(function () {
            Route::get('/clients/{client}/form', WorkshopClientsForm::class)->name('clients.form.edit');
        });
        Route::middleware('permission:workshop.equipment.view')->group(function () {
            Route::get('/equipment', WorkshopEquipmentIndex::class)->name('equipment.index');
            Route::get('/equipment/{equipmentType}', WorkshopEquipmentTypeIndex::class)->name('equipment.type');
        });
        Route::middleware('permission:workshop.equipment.create')->group(function () {
            Route::get('/equipment/{equipmentType}/create', WorkshopEquipmentForm::class)->name('equipment.form');
        });
        Route::middleware('permission:workshop.equipment.edit')->group(function () {
            Route::get('/equipment/{equipmentType}/{equipment}/edit', WorkshopEquipmentForm::class)->name('equipment.form.edit');
        });
        Route::middleware('permission:workshop.equipment.view')->group(function () {
            Route::get('/equipment/{equipmentType}/{equipment}', WorkshopEquipmentShow::class)->name('equipment.show');
        });
        Route::middleware('permission:workshop.quotations.view')->group(function () {
            Route::get('/quotations', WorkshopQuotationsIndex::class)->name('quotations.index');
        });
        Route::middleware('permission:workshop.quotations.create')->group(function () {
            Route::get('/quotations/form', WorkshopQuotationsForm::class)->name('quotations.form');
        });
        Route::middleware('permission:workshop.quotations.edit')->group(function () {
            Route::get('/quotations/{quotation}/form', WorkshopQuotationsForm::class)->name('quotations.form.edit');
        });
        Route::middleware('permission:workshop.quotations.view')->group(function () {
            Route::get('/quotations/{quotation}/print', WorkshopQuotationsPrint::class)->name('quotations.print');
            Route::get('/quotations/{quotation}/pdf', [WorkshopPdfController::class, 'quotation'])->name('quotations.pdf');
            Route::get('/quotations/{quotation}', WorkshopQuotationsShow::class)->name('quotations.show');
        });
        Route::middleware('permission:workshop.quotation_service_types.view')->group(function () {
            Route::get('/service-types', WorkshopQuotationServiceTypesIndex::class)->name('quotation-service-types.index');
            Route::get('/service-types/{quotationServiceType}', WorkshopQuotationServiceTypesShow::class)->name('quotation-service-types.show');
        });
        Route::middleware('permission:workshop.work-orders.view')->group(function () {
            Route::get('/work-orders', WorkshopWorkOrdersIndex::class)->name('work-orders.index');
        });
        Route::middleware('permission:workshop.work-orders.associated-documents.view')->group(function () {
            Route::get('/work-orders/associated-documents', WorkshopAssociatedDocumentsIndex::class)
                ->name('work-orders.associated-documents.index');
        });
        Route::middleware('permission:workshop.work-orders.create')->group(function () {
            Route::get('/work-orders/form', WorkshopWorkOrdersForm::class)->name('work-orders.form');
        });
        Route::middleware('permission:workshop.work-orders.edit')->group(function () {
            Route::get('/work-orders/{workOrder}/form', WorkshopWorkOrdersForm::class)->name('work-orders.form.edit');
        });
        Route::middleware('permission:workshop.work-orders.view')->group(function () {
            Route::get('/work-orders/{workOrder}/print', WorkshopWorkOrdersPrint::class)->name('work-orders.print');
            Route::get('/work-orders/{workOrder}/pdf', [WorkshopPdfController::class, 'workOrder'])->name('work-orders.pdf');
            Route::get('/work-orders/{workOrder}', WorkshopWorkOrdersShow::class)->name('work-orders.show');
        });
        Route::middleware('permission:workshop.remissions.view')->group(function () {
            Route::get('/remissions', WorkshopRemissionsIndex::class)->name('remissions.index');
        });
        Route::middleware('permission:workshop.advance-payments.view')->group(function () {
            Route::get('/advance-payments', WorkshopAdvancePaymentsIndex::class)->name('advance-payments.index');
            Route::get('/advance-payments/{workOrder}', WorkshopAdvancePaymentsShow::class)->name('advance-payments.show');
        });
        Route::middleware('permission:workshop.remissions.create')->group(function () {
            Route::get('/remissions/form', WorkshopRemissionsForm::class)->name('remissions.form');
        });
        Route::middleware('permission:workshop.remissions.edit')->group(function () {
            Route::get('/remissions/{remission}/form', WorkshopRemissionsForm::class)->name('remissions.form.edit');
        });
        Route::middleware('permission:workshop.remissions.view')->group(function () {
            Route::get('/remissions/{remission}/print', WorkshopRemissionsPrint::class)->name('remissions.print');
            Route::get('/remissions/{remission}/pdf', [WorkshopPdfController::class, 'remission'])->name('remissions.pdf');
            Route::get('/remissions/{remission}', WorkshopRemissionsShow::class)->name('remissions.show');
        });
    });

    // Gestión de eventos — Equipos y roles (solo iglesias y superAdmin)
    Route::prefix('events')->name('admin.events.')->group(function () {
        Route::middleware('role_or_permission:events.events.view|events.schedule.view')->group(function () {
            Route::get('/', EventsIndex::class)->name('index');
        });

        Route::middleware('permission:events.schedule.view')->group(function () {
            Route::get('/schedule', EventsScheduleIndex::class)->name('schedule.index');
            Route::get('/schedule/events', ScheduleEventsFeedController::class)->name('schedule.feed');
            Route::get('/schedule/{event}', EventsScheduleShow::class)->name('schedule.show');
        });

        Route::middleware('permission:events.events.view')->group(function () {
            Route::get('/manage', EventsManageIndex::class)->name('manage.index');
        });
        Route::middleware('permission:events.events.create')->group(function () {
            Route::get('/manage/{eventCategory}/create', EventsManageForm::class)->name('manage.category.create');
        });
        Route::middleware('permission:events.events.edit')->group(function () {
            Route::get('/manage/{eventCategory}/{event}/edit', EventsManageForm::class)->name('manage.category.edit');
        });
        Route::middleware('permission:events.events.view')->group(function () {
            Route::get('/manage/{eventCategory}/{event}', EventsManageShow::class)->name('manage.category.show');
            Route::get('/manage/{eventCategory}', EventsManageCategoryIndex::class)->name('manage.category.index');
        });

        Route::middleware('permission:events.team_roles.view')->group(function () {
            Route::get('/team-roles', EventsTeamRolesIndex::class)->name('team-roles.index');
        });
        Route::middleware('permission:events.team_roles.create')->group(function () {
            Route::get('/team-roles/create', EventsTeamRolesForm::class)->name('team-roles.create');
        });
        Route::middleware('permission:events.team_roles.edit')->group(function () {
            Route::get('/team-roles/{eventTeamRole}/edit', EventsTeamRolesForm::class)->name('team-roles.edit');
        });
        Route::middleware('permission:events.team_roles.view')->group(function () {
            Route::get('/team-roles/{eventTeamRole}', EventsTeamRolesShow::class)->name('team-roles.show');
        });

        Route::middleware('permission:events.teams.view')->group(function () {
            Route::get('/teams', EventsTeamsIndex::class)->name('teams.index');
        });
        Route::middleware('permission:events.teams.create')->group(function () {
            Route::get('/teams/create', EventsTeamsForm::class)->name('teams.create');
        });
        Route::middleware('permission:events.teams.edit')->group(function () {
            Route::get('/teams/{eventTeam}/edit', EventsTeamsForm::class)->name('teams.edit');
        });
        Route::middleware('role_or_permission:events.teams.view|events.schedule.view')->group(function () {
            Route::get('/teams/{eventTeam}', EventsTeamsShow::class)->name('teams.show');
        });
    });

    // Reportes
    Route::prefix('reports')->name('admin.reports.')->group(function () {
        Route::middleware('permission:events.reports.attendance.view')->prefix('events/attendance')->name('events.attendance.')->group(function () {
            Route::get('/', ReportsEventsAttendanceIndex::class)->name('index');
            Route::middleware('permission:reports.export')->group(function () {
                Route::get('/{eventCategory}/{event}/pdf', EventAttendancePdfController::class)->name('pdf');
            });
            Route::get('/{eventCategory}/{event}', ReportsEventsAttendanceShow::class)->name('show');
            Route::get('/{eventCategory}', ReportsEventsAttendanceCategoryIndex::class)->name('category');
        });
    });

    // Configuración
    Route::middleware('permission:settings.view')->prefix('settings')->name('admin.settings.')->group(function () {
        // Configuración — General (solo superAdmin)
        Route::middleware(['role:superAdmin', 'permission:settings.statuses.view'])->group(function () {
            Route::get('/general', SettingsGeneralIndex::class)->name('general.index');
            Route::get('/general/statuses', SettingsStatusesIndex::class)->name('general.statuses.index');
        });

        Route::get('/equipment', SettingsEquipmentIndex::class)->name('equipment.index');
        Route::middleware('permission:settings.equipment_types.view')->group(function () {
            Route::get('/equipment/types', SettingsEquipmentTypesIndex::class)->name('equipment.types');
            Route::get('/equipment/types/{equipmentType}', SettingsEquipmentTypesShow::class)->name('equipment.types.show');
        });
        Route::middleware('permission:settings.brands.view')->group(function () {
            Route::get('/equipment/brands', SettingsBrandsIndex::class)->name('equipment.brands');
            Route::get('/equipment/brands/{brand}', SettingsBrandsShow::class)->name('equipment.brands.show');
        });
        Route::middleware('permission:settings.model_equipment.view')->group(function () {
            Route::get('/equipment/models', SettingsEquipmentModelsIndex::class)->name('equipment.models');
            Route::get('/equipment/models/{equipmentModel}', SettingsEquipmentModelsShow::class)->name('equipment.models.show');
        });
        Route::middleware('permission:settings.attributes.view')->group(function () {
            Route::get('/equipment/attributes', SettingsAttributesIndex::class)->name('equipment.attributes.index');
        });
        Route::middleware('permission:settings.attributes.create')->group(function () {
            Route::get('/equipment/attributes/create', SettingsAttributesForm::class)->name('equipment.attributes.create');
        });
        Route::middleware('permission:settings.attributes.edit')->group(function () {
            Route::get('/equipment/attributes/{attribute}/edit', SettingsAttributesForm::class)->name('equipment.attributes.edit');
        });
        Route::middleware('permission:settings.attributes.view')->group(function () {
            Route::get('/equipment/attributes/{attribute}', SettingsAttributesShow::class)->name('equipment.attributes.show');
        });
        Route::get('/equipment/{section}', SettingsEquipmentSectionIndex::class)->name('equipment.section');

        // Configuración — Productos y servicios (directorios)
        Route::get('/catalog-products', SettingsCatalogProductsIndex::class)->name('catalog-products.index');

        Route::middleware('permission:settings.product_types.view')->group(function () {
            Route::get('/catalog-products/types', SettingsProductTypesIndex::class)->name('catalog-products.product-types.index');
            Route::get('/catalog-products/types/{productType}', SettingsProductTypesShow::class)->name('catalog-products.product-types.show');
        });

        Route::middleware('permission:settings.product_categories.view')->group(function () {
            Route::get('/catalog-products/categories', SettingsProductCategoriesIndex::class)->name('catalog-products.product-categories.index');
            Route::get('/catalog-products/categories/{productCategory}', SettingsProductCategoriesShow::class)->name('catalog-products.product-categories.show');
        });

        Route::middleware('permission:settings.units.view')->group(function () {
            Route::get('/catalog-products/units', SettingsUnitsIndex::class)->name('catalog-products.units.index');
            Route::get('/catalog-products/units/{unit}', SettingsUnitsShow::class)->name('catalog-products.units.show');
        });

        Route::middleware('permission:settings.brands.view')->group(function () {
            Route::get('/catalog-products/brands', SettingsCatalogBrandsIndex::class)->name('catalog-products.brands.index');
        });
        Route::middleware('permission:settings.brands.create')->group(function () {
            Route::get('/catalog-products/brands/create', SettingsCatalogBrandsForm::class)->name('catalog-products.brands.create');
        });
        Route::middleware('permission:settings.brands.edit')->group(function () {
            Route::get('/catalog-products/brands/{brand}/edit', SettingsCatalogBrandsForm::class)->name('catalog-products.brands.edit');
        });
        Route::middleware('permission:settings.brands.view')->group(function () {
            Route::get('/catalog-products/brands/{brand}', SettingsCatalogBrandsShow::class)->name('catalog-products.brands.show');
        });

        // Configuración — Eventos (solo iglesias y superAdmin)
        Route::get('/events', SettingsEventsIndex::class)->name('events.index');

        Route::middleware('permission:settings.event_categories.view')->group(function () {
            Route::get('/events/categories', SettingsEventCategoriesIndex::class)->name('events.event-categories.index');
        });
        Route::middleware('permission:settings.event_categories.create')->group(function () {
            Route::get('/events/categories/create', SettingsEventCategoriesForm::class)->name('events.event-categories.create');
        });
        Route::middleware('permission:settings.event_categories.edit')->group(function () {
            Route::get('/events/categories/{eventCategory}/edit', SettingsEventCategoriesForm::class)->name('events.event-categories.edit');
        });
        Route::middleware('permission:settings.event_categories.view')->group(function () {
            Route::get('/events/categories/{eventCategory}', SettingsEventCategoriesShow::class)->name('events.event-categories.show');
        });

        Route::middleware('permission:settings.attendee_types.view')->group(function () {
            Route::get('/events/attendee-types', SettingsAttendeeTypesIndex::class)->name('events.attendee-types.index');
        });
        Route::middleware('permission:settings.attendee_types.create')->group(function () {
            Route::get('/events/attendee-types/create', SettingsAttendeeTypesForm::class)->name('events.attendee-types.create');
        });
        Route::middleware('permission:settings.attendee_types.edit')->group(function () {
            Route::get('/events/attendee-types/{attendeeType}/edit', SettingsAttendeeTypesForm::class)->name('events.attendee-types.edit');
        });
        Route::middleware('permission:settings.attendee_types.view')->group(function () {
            Route::get('/events/attendee-types/{attendeeType}', SettingsAttendeeTypesShow::class)->name('events.attendee-types.show');
        });
    });

    // Catálogo — Productos y servicios
    Route::middleware('permission:catalog.view')->prefix('catalog')->name('admin.catalog.')->group(function () {
        Route::middleware('permission:catalog.products.view')->group(function () {
            Route::get('/products', CatalogProductsIndex::class)->name('products.index');
        });
        Route::middleware('permission:catalog.products.create')->group(function () {
            Route::get('/products/create', CatalogProductsForm::class)->name('products.create');
        });
        Route::middleware('permission:catalog.products.edit')->group(function () {
            Route::get('/products/{product}/edit', CatalogProductsForm::class)->name('products.edit');
        });
        Route::middleware('permission:catalog.products.view')->group(function () {
            Route::get('/products/{product}', CatalogProductsShow::class)->name('products.show');
        });
    });
    });
});
