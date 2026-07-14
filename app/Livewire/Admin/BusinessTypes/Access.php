<?php

namespace App\Livewire\Admin\BusinessTypes;

use App\Models\Business;
use App\Models\BusinessType;
use App\Models\Role;
use App\Support\BusinessAccess;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\PermissionRegistrar;

#[Layout('layouts.app')]
#[Title('Acceso por negocio')]
class Access extends Component
{
    public ?int $business_type_id = null;

    /** @var list<int> */
    public array $selected_business_ids = [];

    /** @var list<int> */
    public array $selected_role_ids = [];

    /** @var list<string> */
    public array $selected_permissions = [];

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->hasRole('superAdmin') && auth()->user()?->can('business_types.access.view'),
            403
        );

        $first = BusinessType::query()->where('status', true)->orderBy('name')->first();
        $this->business_type_id = $first?->id;
    }

    public function updatedBusinessTypeId(): void
    {
        $this->selected_business_ids = [];
        $this->clearAssignmentFields();
    }

    public function updatedSelectedBusinessIds(): void
    {
        $this->syncAssignmentFieldsFromSelection();
    }

    private function syncAssignmentFieldsFromSelection(): void
    {
        $ids = array_values(array_filter(array_map('intval', $this->selected_business_ids)));

        if ($ids === []) {
            $this->clearAssignmentFields();

            return;
        }

        $businesses = Business::query()
            ->with(['roles', 'permissions'])
            ->where('business_type_id', $this->business_type_id)
            ->whereIn('id', $ids)
            ->get();

        if ($businesses->isEmpty()) {
            $this->clearAssignmentFields();

            return;
        }

        $role_ids   = $businesses->first()->roles->pluck('id');
        $perm_names = $businesses->first()->permissions->pluck('name');

        foreach ($businesses->skip(1) as $business) {
            $role_ids   = $role_ids->intersect($business->roles->pluck('id'));
            $perm_names = $perm_names->intersect($business->permissions->pluck('name'));
        }

        // Livewire usa strings en checkboxes
        $this->selected_role_ids = $role_ids
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();

        $this->selected_permissions = $perm_names->values()->all();
    }

    private function clearAssignmentFields(): void
    {
        $this->selected_role_ids    = [];
        $this->selected_permissions = [];
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->hasRole('superAdmin') && auth()->user()?->can('business_types.access.manage'),
            403
        );

        $this->validate([
            'business_type_id'         => 'required|exists:business_types,id',
            'selected_business_ids'    => 'required|array|min:1',
            'selected_business_ids.*'  => [
                'integer',
                Rule::exists('businesses', 'id')->where(
                    fn ($q) => $q->where('business_type_id', $this->business_type_id)->whereNull('deleted_at')
                ),
            ],
            'selected_role_ids'        => 'array',
            'selected_role_ids.*'      => 'integer|exists:roles,id',
            'selected_permissions'     => 'array',
            'selected_permissions.*'   => 'string|exists:permissions,name',
        ], [
            'business_type_id.required'      => 'Selecciona un tipo de negocio.',
            'selected_business_ids.required' => 'Selecciona al menos un negocio.',
            'selected_business_ids.min'      => 'Selecciona al menos un negocio.',
        ]);

        $system_roles = BusinessAccess::systemRoleNames();
        $role_ids     = Role::query()
            ->whereIn('id', $this->selected_role_ids)
            ->whereNotIn('name', $system_roles)
            ->pluck('id')
            ->all();

        $businesses = Business::query()
            ->whereIn('id', $this->selected_business_ids)
            ->get();

        foreach ($businesses as $business) {
            BusinessAccess::syncBusinessAccess($business, $role_ids, $this->selected_permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $count = $businesses->count();
        $this->clearAssignmentFields();
        $this->syncAssignmentFieldsFromSelection();

        $this->dispatch('swal', [
            'title' => "Acceso actualizado en {$count} negocio(s).",
            'icon'  => 'success',
        ]);
    }

    public function toggleModule(string $module_key): void
    {
        abort_unless(
            auth()->user()?->hasRole('superAdmin') && auth()->user()?->can('business_types.access.manage'),
            403
        );

        $modules      = $this->assignableModules();
        $module_perms = array_keys($modules[$module_key]['permissions'] ?? []);

        if ($module_perms === []) {
            return;
        }

        $all_selected = collect($module_perms)->every(fn ($p) => in_array($p, $this->selected_permissions, true));

        if ($all_selected) {
            $this->selected_permissions = array_values(array_diff($this->selected_permissions, $module_perms));
        } else {
            $this->selected_permissions = array_values(array_unique([...$this->selected_permissions, ...$module_perms]));
        }
    }

    public function render()
    {
        $business_types = BusinessType::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', BusinessAccess::systemRoleNames())
            ->orderBy('name')
            ->get();

        $modules = $this->assignableModules();

        $selected_type = $this->business_type_id
            ? $business_types->firstWhere('id', $this->business_type_id)
            : null;

        $businesses_for_type = $this->business_type_id
            ? Business::query()
                ->where('business_type_id', $this->business_type_id)
                ->orderBy('name')
                ->get()
            : collect();

        $assigned_businesses = $this->business_type_id
            ? Business::query()
                ->where('business_type_id', $this->business_type_id)
                ->with(['roles', 'permissions', 'business_type'])
                ->orderBy('name')
                ->get()
            : collect();

        return view('livewire.admin.business-types.access', compact(
            'business_types',
            'roles',
            'modules',
            'selected_type',
            'businesses_for_type',
            'assigned_businesses'
        ));
    }

    /** @return array<string, array{name: string, permissions: array<string, string>}> */
    private function assignableModules(): array
    {
        $platform_only = config('permissions.platform_only_permissions', []);

        return collect(config('permissions.modules', []))
            ->map(function (array $module) use ($platform_only) {
                $module['permissions'] = array_filter(
                    $module['permissions'],
                    fn (string $label, string $name) => ! in_array($name, $platform_only, true),
                    ARRAY_FILTER_USE_BOTH
                );

                return $module['permissions'] === [] ? null : $module;
            })
            ->filter()
            ->all();
    }
}
