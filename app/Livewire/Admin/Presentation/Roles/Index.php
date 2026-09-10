<?php

namespace App\Livewire\Admin\Presentation\Roles;

use App\Support\Presentation\AccessDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Roles')]
class Index extends Component
{
    public bool $showModal = false;

    public string $name = '';

    public string $description = '';

    /** @var list<string> */
    public array $selected_permissions = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
    }

    public function openCreate(): void
    {
        $this->reset(['name', 'description', 'selected_permissions']);
        $this->showModal = true;
        $this->resetValidation();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:80',
            'description' => 'nullable|string|max:160',
            'selected_permissions' => 'array',
        ], [
            'name.required' => 'El nombre del rol es obligatorio.',
        ]);

        AccessDemoData::addRole([
            'id' => 'role-custom-'.uniqid(),
            'name' => trim($this->name),
            'description' => trim($this->description) !== '' ? trim($this->description) : 'Rol de demostración.',
            'permissions' => $this->selected_permissions,
        ]);

        $this->closeModal();
        $this->dispatch('swal', ['title' => 'Rol creado', 'icon' => 'success']);
    }

    public function render()
    {
        return view('livewire.admin.presentation.roles.index', [
            'roles' => AccessDemoData::roles(),
            'permission_labels' => AccessDemoData::permissionLabels(),
        ]);
    }
}
