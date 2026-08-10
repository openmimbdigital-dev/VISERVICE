<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Gestión de Negocios (solo superAdmin — no asignar a roles de negocio).
     *
     * @return list<string>
     */
    private function businessManagementPermissions(): array
    {
        return [
            'organization_types.view', 'organization_types.create', 'organization_types.edit', 'organization_types.delete',
            'business_types.view', 'business_types.create', 'business_types.edit', 'business_types.delete',
            'organization_types.access.view', 'organization_types.access.manage',
            'businesses.manage_modules',
        ];
    }

    /** @return list<string> */
    private function teamPositionPermissions(): array
    {
        return [
            'team_positions.view', 'team_positions.create', 'team_positions.edit', 'team_positions.delete',
        ];
    }

    /** @return list<string> */
    private function participantPermissions(): array
    {
        return [
            'participants.view', 'participants.create', 'participants.edit', 'participants.delete',
            'participants.roles.view', 'participants.roles.create', 'participants.roles.edit', 'participants.roles.delete',
        ];
    }

    /** @return list<string> */
    private function businessPaymentSettingsPermissions(): array
    {
        return [
            'business_payment_methods.view', 'business_payment_methods.create', 'business_payment_methods.edit', 'business_payment_methods.delete',
            'business_bank_accounts.view', 'business_bank_accounts.create', 'business_bank_accounts.edit', 'business_bank_accounts.delete',
        ];
    }

    /** @return list<string> */
    private function catalogProductsPermissions(): array
    {
        return [
            'catalog.view',
            'catalog.products.view', 'catalog.products.create', 'catalog.products.edit', 'catalog.products.delete',
        ];
    }

    /** @return list<string> */
    private function catalogProductsSettingsPermissions(): array
    {
        return [
            'settings.product_types.view', 'settings.product_types.create', 'settings.product_types.edit', 'settings.product_types.delete',
            'settings.product_categories.view', 'settings.product_categories.create', 'settings.product_categories.edit', 'settings.product_categories.delete',
            'settings.units.view', 'settings.units.create', 'settings.units.edit', 'settings.units.delete',
        ];
    }

    /** @return list<string> */
    private function churchEventsSettingsPermissions(): array
    {
        return [
            'settings.event_categories.view', 'settings.event_categories.create',
            'settings.event_categories.edit', 'settings.event_categories.delete',
            'settings.attendee_types.view', 'settings.attendee_types.create',
            'settings.attendee_types.edit', 'settings.attendee_types.delete',
        ];
    }

    /** @return list<string> */
    private function eventsManagementPermissions(): array
    {
        return [
            'events.events.view',
            'events.events.create',
            'events.events.edit',
            'events.events.delete',
            'events.schedule.view',
        ];
    }

    /** @return list<string> */
    private function eventsAttendancePermissions(): array
    {
        return [
            'events.attendance.start',
            'events.attendance.close',
        ];
    }

    /** @return list<string> */
    private function eventsReportsPermissions(): array
    {
        return [
            'events.reports.attendance.view',
        ];
    }

    /** @return list<string> */
    private function eventTeamsPermissions(): array
    {
        return [
            'events.teams.view',
            'events.teams.create',
            'events.teams.edit',
            'events.teams.delete',
            'events.team_roles.view',
            'events.team_roles.create',
            'events.team_roles.edit',
            'events.team_roles.delete',
        ];
    }

    /** @return list<string> */
    private function quotationServiceTypePermissions(): array
    {
        return [
            'workshop.quotation_service_types.view',
            'workshop.quotation_service_types.create',
            'workshop.quotation_service_types.edit',
            'workshop.quotation_service_types.delete',
        ];
    }

    /** @return list<string> */
    private function quotationPermissions(): array
    {
        return [
            'workshop.quotations.view',
            'workshop.quotations.create',
            'workshop.quotations.edit',
            'workshop.quotations.delete',
            ...$this->quotationServiceTypePermissions(),
        ];
    }

    /** @return list<string> */
    private function workshopPermissions(): array
    {
        return [
            'workshop.view',
            'workshop.clients.view', 'workshop.clients.create', 'workshop.clients.edit',
            'workshop.clients.delete', 'workshop.clients.activate', 'workshop.clients.deactivate',
            'workshop.equipment.view', 'workshop.equipment.create', 'workshop.equipment.edit',
            'workshop.equipment.delete',
            ...$this->quotationPermissions(),
            ...$this->workOrderPermissions(),
            ...$this->remissionPermissions(),
            ...$this->advancePaymentPermissions(),
        ];
    }

    /** @return list<string> */
    private function advancePaymentPermissions(): array
    {
        return [
            'workshop.advance-payments.view',
            'workshop.advance-payments.pay',
            'workshop.advance-payments.void',
        ];
    }

    /** @return list<string> */
    private function workOrderPermissions(): array
    {
        return [
            'workshop.work-orders.view',
            'workshop.work-orders.create',
            'workshop.work-orders.edit',
            'workshop.work-orders.delete',
            ...$this->associatedDocumentOtPermissions(),
        ];
    }

    /** @return list<string> */
    private function associatedDocumentOtPermissions(): array
    {
        return [
            'workshop.work-orders.associated-documents.view',
            'workshop.work-orders.associated-documents.create',
            'workshop.work-orders.associated-documents.edit',
            'workshop.work-orders.associated-documents.delete',
        ];
    }

    /** @return list<string> */
    private function remissionPermissions(): array
    {
        return [
            'workshop.remissions.view',
            'workshop.remissions.create',
            'workshop.remissions.edit',
            'workshop.remissions.delete',
        ];
    }

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        // ── Permisos ────────────────────────────────────────────────────────

        $perms = collect([
            // Usuarios
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'users.activate', 'users.deactivate',
            // Empresas
            'businesses.view', 'businesses.create', 'businesses.edit', 'businesses.delete',
            'businesses.activate', 'businesses.deactivate', 'businesses.manage_addresses',
            // Reportes
            'reports.view', 'reports.export',
            // Configuración
            'settings.view', 'settings.edit',
            // Configuración — Eventos
            ...$this->churchEventsSettingsPermissions(),
            // Gestión de eventos — Eventos y agenda
            ...$this->eventsManagementPermissions(),
            ...$this->eventsAttendancePermissions(),
            ...$this->eventsReportsPermissions(),
            // Gestión de eventos — Equipos
            ...$this->eventTeamsPermissions(),
            // Configuración — Atributos de equipo
            'settings.attributes.view', 'settings.attributes.create',
            'settings.attributes.edit', 'settings.attributes.delete',
            // Configuración — Marcas de equipo
            'settings.brands.view', 'settings.brands.create',
            'settings.brands.edit', 'settings.brands.delete',
            // Configuración — Modelos de equipo
            'settings.model_equipment.view', 'settings.model_equipment.create',
            'settings.model_equipment.edit', 'settings.model_equipment.delete',
            // Configuración — Tipos de equipo (solo superAdmin)
            'settings.equipment_types.view', 'settings.equipment_types.create',
            'settings.equipment_types.edit', 'settings.equipment_types.delete',
            // Configuración — Estados (solo superAdmin)
            'settings.statuses.view', 'settings.statuses.create',
            'settings.statuses.edit', 'settings.statuses.delete',
            // Roles
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'permissions.view', 'permissions.assign',
            // Suscripciones
            'subscriptions.view', 'subscriptions.create', 'subscriptions.edit', 'subscriptions.cancel',
            'subscriptions.plans.view', 'subscriptions.plans.manage',
            'subscriptions.invoices.view', 'subscriptions.invoices.manage',
            // Taller — Clientes
            'workshop.clients.view', 'workshop.clients.create', 'workshop.clients.edit',
            'workshop.clients.delete', 'workshop.clients.activate', 'workshop.clients.deactivate',
            // Taller — Equipos
            'workshop.equipment.view', 'workshop.equipment.create', 'workshop.equipment.edit',
            'workshop.equipment.delete',
            'workshop.view',
            ...$this->quotationPermissions(),
            ...$this->workOrderPermissions(),
            ...$this->remissionPermissions(),
            ...$this->advancePaymentPermissions(),
            // Gestión de Negocios (solo superAdmin)
            ...$this->businessManagementPermissions(),
            // Negocios — Cargos del equipo
            ...$this->teamPositionPermissions(),
            // Negocios — Participantes
            ...$this->participantPermissions(),
            // Negocios — Pagos y bancos
            ...$this->businessPaymentSettingsPermissions(),
            // Catálogo — Productos y servicios
            ...$this->catalogProductsPermissions(),
            ...$this->catalogProductsSettingsPermissions(),
        ])->mapWithKeys(fn ($name) => [
            $name => Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]),
        ]);

        Permission::query()
            ->where('guard_name', $guard)
            ->whereIn('name', [
                'workshop.advance-payments.create',
                'workshop.advance-payments.edit',
                'workshop.advance-payments.delete',
            ])
            ->delete();

        // ── Roles ───────────────────────────────────────────────────────────

        $superAdmin = Role::firstOrCreate(['name' => 'superAdmin',    'guard_name' => $guard]);
        $admin = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => $guard]);
        $comercio = Role::firstOrCreate(['name' => 'Comercio',      'guard_name' => $guard]);
        $supervisor = Role::firstOrCreate(['name' => 'Supervisor',    'guard_name' => $guard]);
        $operador = Role::firstOrCreate(['name' => 'Operador',      'guard_name' => $guard]);
        $pastor = Role::firstOrCreate(['name' => 'Pastor',        'guard_name' => $guard]);
        $secretario = Role::firstOrCreate(['name' => 'Secretario',    'guard_name' => $guard]);
        $lider = Role::firstOrCreate(['name' => 'Lider de congregacion', 'guard_name' => $guard]);
        $miembro = Role::firstOrCreate(['name' => 'Miembro',       'guard_name' => $guard]);

        // superAdmin: todos los permisos del sistema
        $superAdmin->syncPermissions($perms->values());

        // Miembro: sin permisos (solo identificación de rol)
        $miembro->syncPermissions([]);

        // Administrador y Comercio: métodos de pago y datos bancarios del negocio
        $business_payment_settings = $this->businessPaymentSettingsPermissions();

        // Administrador: todo menos suscripciones (las gestiona solo el superAdmin).
        // OT CRUD: workshop.work-orders.* vía workOrderPermissions().
        $admin->syncPermissions($perms->only([
            'users.view', 'users.create', 'users.edit',
            'businesses.view', 'businesses.create', 'businesses.edit',
            'reports.view', 'reports.export',
            'settings.view', 'settings.edit',
            'settings.attributes.view', 'settings.attributes.create',
            'settings.attributes.edit', 'settings.attributes.delete',
            'settings.brands.view', 'settings.brands.create',
            'settings.brands.edit', 'settings.brands.delete',
            'settings.model_equipment.view', 'settings.model_equipment.create',
            'settings.model_equipment.edit', 'settings.model_equipment.delete',
            'roles.view', 'permissions.view',
            ...$this->workshopPermissions(),
            ...$this->teamPositionPermissions(),
            ...$this->participantPermissions(),
            ...$business_payment_settings,
            ...$this->catalogProductsPermissions(),
            ...$this->catalogProductsSettingsPermissions(),
            ...$this->churchEventsSettingsPermissions(),
            ...$this->eventsManagementPermissions(),
            ...$this->eventsReportsPermissions(),
            ...$this->eventTeamsPermissions(),
        ])->values());

        // Comercio: propietario del negocio registrado vía onboarding.
        // OT CRUD: mismos workshop.work-orders.* que Administrador.
        $comercio->syncPermissions($perms->only([
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'businesses.view', 'businesses.edit',
            'reports.view', 'reports.export',
            'settings.view', 'settings.edit',
            'settings.attributes.view', 'settings.attributes.create',
            'settings.attributes.edit', 'settings.attributes.delete',
            'settings.brands.view', 'settings.brands.create',
            'settings.brands.edit', 'settings.brands.delete',
            'settings.model_equipment.view', 'settings.model_equipment.create',
            'settings.model_equipment.edit', 'settings.model_equipment.delete',
            ...$this->workshopPermissions(),
            ...$this->teamPositionPermissions(),
            ...$business_payment_settings,
            ...$this->catalogProductsPermissions(),
            ...$this->catalogProductsSettingsPermissions(),
            ...$this->churchEventsSettingsPermissions(),
            ...$this->eventsManagementPermissions(),
            ...$this->eventsReportsPermissions(),
            ...$this->eventTeamsPermissions(),
        ])->values());

        // Supervisor / Operador: sin permisos de órdenes de trabajo (solo Admin, Comercio y superAdmin).
        $supervisor->syncPermissions($perms->only([
            'users.view', 'businesses.view', 'reports.view', 'roles.view', 'permissions.view',
            'team_positions.view',
        ])->values());

        $operador->syncPermissions($perms->only([
            'users.view', 'businesses.view', 'reports.view',
        ])->values());

        // Pastor: liderazgo pastoral
        $pastor->syncPermissions($perms->only([
            'users.view', 'users.create', 'users.edit', 'users.activate', 'users.deactivate',
            'businesses.view', 'businesses.edit',
            'reports.view', 'reports.export',
            'settings.view',
            'businesses.view', 'businesses.create', 'businesses.edit', 'businesses.delete',
            'businesses.activate', 'businesses.deactivate',
            'roles.view', 'roles.create',
            'permissions.view', 'permissions.assign',
            ...$this->teamPositionPermissions(),
            ...$this->participantPermissions(),
            ...$this->churchEventsSettingsPermissions(),
            ...$this->eventsManagementPermissions(),
            ...$this->eventsAttendancePermissions(),
            ...$this->eventsReportsPermissions(),
            ...$this->eventTeamsPermissions(),
        ])->values());

        // Secretario: administración
        $secretario->syncPermissions($perms->only([
            'users.view', 'users.create', 'users.edit',
            'businesses.view',
            'reports.view',
            'settings.view',
            'team_positions.view', 'team_positions.create', 'team_positions.edit',
            ...$this->churchEventsSettingsPermissions(),
            'events.events.view', 'events.events.create', 'events.events.edit',
            'events.schedule.view',
            ...$this->eventTeamsPermissions(),
        ])->values());

        // Líder de congregación: operación de campo
        $lider->syncPermissions($perms->only([
            'users.view',
            'businesses.view',
            'reports.view',
            'settings.view',
            'settings.event_categories.view',
            'settings.attendee_types.view',
            'events.events.view',
            'events.schedule.view',
            'events.teams.view',
            'events.team_roles.view',
        ])->values());

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
