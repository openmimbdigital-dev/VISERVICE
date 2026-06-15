<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Roles y Permisos')]
class Index extends Component
{
    // Sistema: estos roles no se pueden eliminar ni renombrar
    const PROTECTED_ROLES = ['superAdmin', 'Comercio'];

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

    public function openCreate(): void
    {
        $this->selected_id    = null;
        $this->name           = '';
        $this->selectedPerms  = [];
        $this->resetValidation();
        $this->showModal      = true;
    }

    public function openEdit(int $id): void
    {
        $role = Role::with('permissions')->findOrFail($id);

        $this->selected_id   = $role->id;
        $this->name          = $role->name;
        $this->selectedPerms = $role->permissions->pluck('name')->toArray();
        $this->resetValidation();
        $this->showModal     = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->selected_id) {
            $role = Role::findOrFail($this->selected_id);

            // No renombrar roles protegidos
            if (!in_array($role->name, self::PROTECTED_ROLES)) {
                $role->update(['name' => $this->name]);
            }

            // superAdmin mantiene todos los permisos
            if ($role->name !== 'superAdmin') {
                $role->syncPermissions($this->selectedPerms);
            }

            $this->dispatch('swal', ['title' => 'Rol actualizado.', 'icon' => 'success']);
        } else {
            $role = Role::create(['name' => $this->name, 'guard_name' => 'web']);
            $role->syncPermissions($this->selectedPerms);

            $this->dispatch('swal', ['title' => 'Rol creado.', 'icon' => 'success']);
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $role = Role::withCount('users')->findOrFail($id);

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
        $roles = Role::withCount(['permissions', 'users'])
            ->with('permissions')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('id')
            ->get();

        $modules        = config('permissions.modules', []);
        $totalPerms     = Permission::count();
        $allPermissions = Permission::orderBy('name')->get();

        return view('livewire.admin.roles.index', compact('roles', 'modules', 'totalPerms', 'allPermissions'));
    }
}
