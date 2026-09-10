<?php

namespace App\Livewire\Admin\Presentation\Users;

use App\Support\Presentation\AccessDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Usuarios')]
class Index extends Component
{
    public bool $showModal = false;

    public string $name = '';

    public string $email = '';

    public string $role = '';

    public string $status = 'active';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
    }

    public function openCreate(): void
    {
        $this->reset(['name', 'email', 'status']);
        $this->role = AccessDemoData::roleNames()[0] ?? '';
        $this->status = 'active';
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
            'email' => 'required|email|max:80',
            'role' => 'required|string',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Indica un correo válido.',
            'role.required' => 'El rol es obligatorio.',
        ]);

        AccessDemoData::addUser([
            'id' => 'usr-custom-'.uniqid(),
            'name' => trim($this->name),
            'email' => trim($this->email),
            'role' => $this->role,
            'status' => $this->status,
        ]);

        $this->closeModal();
        $this->dispatch('swal', ['title' => 'Usuario creado', 'icon' => 'success']);
    }

    public function render()
    {
        return view('livewire.admin.presentation.users.index', [
            'users' => AccessDemoData::users(),
            'roles' => AccessDemoData::roleNames(),
        ]);
    }
}
