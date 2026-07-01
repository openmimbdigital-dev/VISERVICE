<?php

namespace App\Livewire\Admin\BusinessTypes;

use App\Models\BusinessType;
use App\Models\Role;
use App\Support\BusinessTypeAccess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\PermissionRegistrar;

#[Layout('layouts.app')]
#[Title('Acceso por tipo de negocio')]
class Access extends Component
{
    public ?int $business_type_id = null;

    /** @var list<int> */
    public array $selected_role_ids = [];

    /** @var list<string> */
    public array $selected_permissions = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('business_types.access.view'), 403);

        $first = BusinessType::query()->where('status', true)->orderBy('name')->first();
        $this->business_type_id = $first?->id;

        if ($this->business_type_id) {
            $this->loadSelections();
        }
    }

    public function updatedBusinessTypeId(): void
    {
        $this->loadSelections();
    }

    private function loadSelections(): void
    {
        if (! $this->business_type_id) {
            $this->selected_role_ids      = [];
            $this->selected_permissions   = [];

            return;
        }

        $type = BusinessType::with(['roles', 'permissions'])->find($this->business_type_id);

        if (! $type) {
            return;
        }

        $this->selected_role_ids    = $type->roles->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->selected_permissions = $type->permissions->pluck('name')->all();
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('business_types.access.manage'), 403);

        $this->validate([
            'business_type_id'       => 'required|exists:business_types,id',
            'selected_role_ids'      => 'array',
            'selected_role_ids.*'    => 'integer|exists:roles,id',
            'selected_permissions'   => 'array',
            'selected_permissions.*' => 'string|exists:permissions,name',
        ], [
            'business_type_id.required' => 'Selecciona un tipo de negocio.',
        ]);

        $type = BusinessType::findOrFail($this->business_type_id);

        $system_roles = BusinessTypeAccess::systemRoleNames();
        $role_ids     = Role::query()
            ->whereIn('id', $this->selected_role_ids)
            ->whereNotIn('name', $system_roles)
            ->pluck('id')
            ->all();

        BusinessTypeAccess::syncBusinessTypeAccess($type, $role_ids, $this->selected_permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->dispatch('swal', [
            'title' => "Acceso actualizado para {$type->name}.",
            'icon'  => 'success',
        ]);
    }

    public function toggleModule(string $module_key): void
    {
        abort_unless(auth()->user()?->can('business_types.access.manage'), 403);

        $modules     = config('permissions.modules', []);
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
            ->whereNotIn('name', BusinessTypeAccess::systemRoleNames())
            ->orderBy('name')
            ->get();

        $modules = config('permissions.modules', []);

        $selected_type = $this->business_type_id
            ? $business_types->firstWhere('id', $this->business_type_id)
            : null;

        return view('livewire.admin.business-types.access', compact('business_types', 'roles', 'modules', 'selected_type'));
    }
}
