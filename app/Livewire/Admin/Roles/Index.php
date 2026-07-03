<?php

namespace App\Livewire\Admin\Roles;

use App\Models\Business;
use App\Models\Permission;
use App\Models\Role;
use App\Support\BusinessAccess;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Roles y Permisos')]
class Index extends Component
{
    // Sistema: estos roles no se pueden eliminar ni renombrar
    const PROTECTED_ROLES = ['superAdmin'];

    public bool $showModal      = false;
    public ?int $selected_id    = null;
    public string $name         = '';
    public array $selectedPerms = [];
    public string $search       = '';
    public ?int $expandedRole   = null;

    protected function rules(): array
    {
        $unique = $this->selected_id
            ? 'unique:roles,name,' . $this->selected_id
            : 'unique:roles,name';

        return [
            'name'          => "required|string|max:50|{$unique}",
            'selectedPerms' => 'array',
        ];
    }

    protected $messages = [
        'name.required' => 'El nombre del rol es obligatorio.',
        'name.unique'   => 'Ya existe un rol con ese nombre.',
        'name.max'      => 'El nombre no puede superar 50 caracteres.',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('roles.view'), 403);
    }

    /** @return list<string> */
    private function allowedPermissionNames(): array
    {
        return BusinessAccess::manageablePermissionNamesForUser(auth()->user());
    }

    private function findManageableRole(int $id): Role
    {
        $role = Role::with('permissions')->findOrFail($id);

        abort_unless(BusinessAccess::roleManageableByUser($role, auth()->user()), 403);

        return $role;
    }

    /** @param list<string> $permissions @return list<string> */
    private function filterAllowedPermissions(array $permissions): array
    {
        return array_values(array_intersect($permissions, $this->allowedPermissionNames()));
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()?->can('roles.create'), 403);
        $this->selected_id    = null;
        $this->name           = '';
        $this->selectedPerms  = [];
        $this->resetValidation();
        $this->showModal      = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()?->can('roles.view'), 403);

        $role = $this->findManageableRole($id);

        $this->selected_id   = $role->id;
        $this->name          = $role->name;
        $this->selectedPerms = $this->filterAllowedPermissions(
            $role->permissions->pluck('name')->all()
        );
        $this->resetValidation();
        $this->showModal     = true;
    }

    public function save(): void
    {
        $this->validate();

        $allowedPerms = $this->filterAllowedPermissions($this->selectedPerms);

        if ($this->selected_id) {
            abort_unless(
                auth()->user()?->can('roles.edit') || auth()->user()?->can('permissions.assign'),
                403
            );

            $role = $this->findManageableRole($this->selected_id);

            if ($role->name === 'superAdmin') {
                $this->closeModal();

                return;
            }

            if (! in_array($role->name, self::PROTECTED_ROLES) && $this->name !== $role->name) {
                abort_unless(auth()->user()?->can('roles.edit'), 403);
                $role->update(['name' => $this->name]);
            }

            if (auth()->user()?->can('permissions.assign')) {
                if (auth()->user()->hasRole('superAdmin')) {
                    $role->syncPermissions($allowedPerms);
                } else {
                    $existing  = $role->permissions->pluck('name')->all();
                    $outside   = array_diff($existing, $this->allowedPermissionNames());
                    $role->syncPermissions([...$outside, ...$allowedPerms]);
                }
            }

            $this->dispatch('swal', ['title' => 'Rol actualizado.', 'icon' => 'success']);
        } else {
            abort_unless(auth()->user()?->can('roles.create'), 403);

            $role = Role::create(['name' => $this->name, 'guard_name' => 'web']);

            if (! auth()->user()->hasRole('superAdmin')) {
                $business_id = BusinessAccess::primaryBusinessId(auth()->user());
                if ($business_id) {
                    Business::query()->find($business_id)?->roles()->syncWithoutDetaching([$role->id]);
                }
            }

            if (auth()->user()?->can('permissions.assign')) {
                $role->syncPermissions($allowedPerms);
            }

            $this->dispatch('swal', ['title' => 'Rol creado.', 'icon' => 'success']);
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('roles.delete'), 403);

        $role = $this->findManageableRole($id);
        $role->loadCount('users');

        if (in_array($role->name, self::PROTECTED_ROLES)) {
            $this->dispatch('swal', ['title' => 'Rol protegido', 'text' => 'Este rol del sistema no puede eliminarse.', 'icon' => 'error']);
            return;
        }

        if ($role->users_count > 0) {
            $this->dispatch('swal', ['title' => 'Rol en uso', 'text' => "Este rol tiene {$role->users_count} usuario(s) asignado(s).", 'icon' => 'error']);
            return;
        }

        $role->delete();
        $this->dispatch('swal', ['title' => 'Rol eliminado.', 'icon' => 'warning']);
    }

    public function toggleExpand(int $id): void
    {
        abort_unless(auth()->user()?->can('permissions.view'), 403);

        $this->findManageableRole($id);

        $this->expandedRole = $this->expandedRole === $id ? null : $id;
    }

    public function closeModal(): void
    {
        $this->showModal     = false;
        $this->selected_id   = null;
        $this->name          = '';
        $this->selectedPerms = [];
        $this->resetValidation();
    }

    public function render()
    {
        $user = auth()->user();

        $manageableRoleIds = BusinessAccess::manageableRolesForUser($user)->pluck('id');

        $roles = Role::withCount(['permissions', 'users'])
            ->with('permissions')
            ->whereIn('id', $manageableRoleIds)
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('id')
            ->get();

        $modules            = BusinessAccess::manageableModulesForUser($user);
        $allowedPermNames   = $this->allowedPermissionNames();
        $totalPerms         = count($allowedPermNames);
        $allPermissions     = Permission::query()
            ->whereIn('name', $allowedPermNames)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.roles.index', compact('roles', 'modules', 'totalPerms', 'allPermissions', 'allowedPermNames'));
    }
}
