<?php

namespace App\Livewire\Admin\User;

use App\Livewire\Concerns\ConfirmsDeletionWithLivewireAlert;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Usuarios')]
class Index extends Component
{
    use ConfirmsDeletionWithLivewireAlert;
    use WithPagination;

    // Filtros
    public string $search = '';
    public string $filter_role = '';
    public string $filter_status = '';

    // Modal
    public bool $showModal = false;
    public bool $editing = false;
    public ?int $selected_id = null;

    // Campos del formulario
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $username = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $phone_number = '';
    public bool $status = true;
    public string $role = '';
    private ?bool $original_status = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('users.view'), 403);
    }

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingFilterRole(): void   { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    private function isSuperAdmin(): bool
    {
        return auth()->user()->hasRole('superAdmin');
    }

    private function baseQuery()
    {
        $query = User::with('roles');

        if (! $this->isSuperAdmin()) {
            $query->where('business_id', auth()->user()->business_id);
        }

        return $query;
    }

    // El usuario principal de un negocio es el de menor ID en ese business_id
    private function primaryIdForBusiness(?int $businessId): ?int
    {
        if (! $businessId) return null;

        return User::where('business_id', $businessId)->orderBy('id')->value('id');
    }

    private function isPrimaryUser(User $user): bool
    {
        return $user->id === $this->primaryIdForBusiness($user->business_id);
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()->can('users.create'), 403);

        $this->reset(['first_name','last_name','email','username','password','password_confirmation','phone_number','role']);
        $this->status          = true;
        $this->original_status = null;
        $this->editing         = false;
        $this->selected_id     = null;
        $this->showModal       = true;
        $this->resetErrorBag();
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()->can('users.edit'), 403);

        $user = $this->findAuthorized($id);

        $this->selected_id           = $user->id;
        $this->first_name            = $user->first_name;
        $this->last_name             = $user->last_name ?? '';
        $this->email                 = $user->email;
        $this->username              = $user->username;
        $this->phone_number          = $user->phone_number ?? '';
        $this->status                = (bool) $user->status;
        $this->original_status       = (bool) $user->status;
        $this->role                  = $user->getRoleNames()->first() ?? '';
        $this->password              = '';
        $this->password_confirmation = '';
        $this->editing               = true;
        $this->showModal             = true;
        $this->resetErrorBag();
    }

    private function authorizeStatusChange(bool $new_status): void
    {
        if ($new_status) {
            abort_unless(auth()->user()->can('users.activate'), 403);
            return;
        }

        abort_unless(auth()->user()->can('users.deactivate'), 403);
    }

    public function save(): void
    {
        abort_unless(
            $this->editing ? auth()->user()->can('users.edit') : auth()->user()->can('users.create'),
            403
        );
        $uniqueEmail    = 'unique:users,email'    . ($this->editing ? ",{$this->selected_id}" : '');
        $uniqueUsername = 'unique:users,username' . ($this->editing ? ",{$this->selected_id}" : '');

        $rules = [
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'email'        => "required|email|max:150|{$uniqueEmail}",
            'username'     => "required|string|min:4|max:50|{$uniqueUsername}",
            'phone_number' => 'nullable|string|max:20',
        ];

        // superAdmin elige el rol; Comercio lo asigna automáticamente
        if ($this->isSuperAdmin()) {
            $rules['role'] = 'required|string|exists:roles,name';
        }

        if (! $this->editing) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } elseif ($this->password !== '') {
            $rules['password'] = 'string|min:8|confirmed';
        }

        $this->validate($rules, [
            'first_name.required'  => 'El nombre es obligatorio.',
            'last_name.required'   => 'El apellido es obligatorio.',
            'email.required'       => 'El correo es obligatorio.',
            'email.email'          => 'El correo no es válido.',
            'email.unique'         => 'Este correo ya está en uso.',
            'username.required'    => 'El nombre de usuario es obligatorio.',
            'username.min'         => 'El usuario debe tener al menos 4 caracteres.',
            'username.unique'      => 'Este nombre de usuario ya está en uso.',
            'password.required'    => 'La contraseña es obligatoria.',
            'password.min'         => 'Mínimo 8 caracteres.',
            'password.confirmed'   => 'Las contraseñas no coinciden.',
            'role.required'        => 'Selecciona un rol.',
        ]);

        if ($this->editing) {
            $user = $this->findAuthorized($this->selected_id);

            if ($this->status !== $this->original_status) {
                $this->authorizeStatusChange($this->status);
            }

            $data = [
                'first_name'   => $this->first_name,
                'last_name'    => $this->last_name,
                'email'        => $this->email,
                'username'     => $this->username,
                'phone_number' => $this->phone_number ?: null,
                'status'       => $this->status,
            ];
            if ($this->password !== '') {
                $data['password'] = Hash::make($this->password);
            }
            $user->update($data);

            // Solo superAdmin puede cambiar el rol al editar
            if ($this->isSuperAdmin() && $this->role) {
                $user->syncRoles([$this->role]);
            }

            $this->dispatch('swal', ['title' => 'Usuario actualizado correctamente.', 'icon' => 'success']);
        } else {
            $assignedRole = $this->isSuperAdmin() ? $this->role : 'Comercio';

            $user = User::create([
                'first_name'   => $this->first_name,
                'last_name'    => $this->last_name,
                'email'        => $this->email,
                'username'     => $this->username,
                'phone_number' => $this->phone_number ?: null,
                'status'       => $this->status,
                'password'     => Hash::make($this->password),
                'business_id'  => $this->isSuperAdmin() ? null : auth()->user()->business_id,
            ]);
            $user->assignRole($assignedRole);

            $this->dispatch('swal', ['title' => 'Usuario creado correctamente.', 'icon' => 'success']);
        }

        $this->showModal = false;
    }

    public function toggleStatus(int $id): void
    {
        $user = $this->findAuthorized($id);

        $this->authorizeStatusChange(! $user->status);

        if ($user->id === auth()->id()) {
            $this->dispatch('swal', ['title' => 'No puedes desactivar tu propia cuenta.', 'icon' => 'warning']);
            return;
        }

        // Nadie puede desactivar al usuario principal de un negocio excepto el propio superAdmin
        if (! $this->isSuperAdmin() && $this->isPrimaryUser($user)) {
            $this->dispatch('swal', ['title' => 'No puedes desactivar al usuario principal del comercio.', 'icon' => 'warning']);
            return;
        }

        $user->update(['status' => ! $user->status]);
        $label = $user->fresh()->status ? 'activado' : 'desactivado';
        $this->dispatch('swal', ['title' => "Usuario {$label}.", 'icon' => 'success']);
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->can('users.delete'), 403);

        $this->askDeleteConfirmation($id, '¿Estás seguro de querer eliminar este usuario?');
    }

    protected function onDeleteConfirmed(): void
    {
        try {
            abort_unless(auth()->user()->can('users.delete'), 403);

            $user = $this->findAuthorized($this->delete_id);

            if (! $this->canDelete($user)) {
                $this->alertDeleteError('Este usuario no puede eliminarse.');

                return;
            }

            $user->delete();

            $this->alertDeleteSuccess('Usuario eliminado correctamente.');
        } catch (\Throwable) {
            $this->alertDeleteError('No se pudo eliminar el usuario.');
        }
    }

    public function canDelete(User $user): bool
    {
        if ($user->id === auth()->id()) return false;
        if ($user->hasRole('superAdmin'))  return false;
        if ($this->isPrimaryUser($user))   return false; // fundador del negocio

        return true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    private function findAuthorized(int $id): User
    {
        return $this->baseQuery()->where('id', $id)->firstOrFail();
    }

    public function render()
    {
        $users = $this->baseQuery()
            ->when($this->search, fn ($q) => $q->where(fn ($s) =>
                $s->where('first_name', 'like', "%{$this->search}%")
                  ->orWhere('last_name',  'like', "%{$this->search}%")
                  ->orWhere('email',      'like', "%{$this->search}%")
                  ->orWhere('username',   'like', "%{$this->search}%")
            ))
            ->when($this->filter_status !== '', fn ($q) => $q->where('status', (bool) $this->filter_status))
            ->when($this->filter_role,  fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', $this->filter_role)))
            ->latest()
            ->paginate(15);

        // Pre-computar el ID del usuario principal por cada negocio presente en la página
        $businessIds = $users->pluck('business_id')->filter()->unique()->values()->all();
        $primaryUserIds = [];
        if (! empty($businessIds)) {
            $primaryUserIds = User::whereIn('business_id', $businessIds)
                ->selectRaw('MIN(id) as id, business_id')
                ->groupBy('business_id')
                ->pluck('id')
                ->all();
        }

        $stats = [
            'total'      => $this->baseQuery()->count(),
            'active'     => $this->baseQuery()->where('status', true)->count(),
            'this_month' => $this->baseQuery()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at',  now()->year)
                ->count(),
        ];

        // superAdmin elige rol; Comercio no necesita selector (se asigna automáticamente)
        $roles = $this->isSuperAdmin() ? Role::orderBy('name')->get() : collect();

        return view('livewire.admin.user.index', compact('users', 'stats', 'roles', 'primaryUserIds'));
    }
}
