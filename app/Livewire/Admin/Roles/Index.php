<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    public int $rolesTotal = 0;

    public int $rolesThisMonth = 0;

    public function mount(): void
    {
        $this->rolesTotal = Role::query()->count();
        $this->rolesThisMonth = Role::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    public function render()
    {
        return view('livewire.admin.roles.index')
            ->layout('layouts.app', [
                'title' => 'Gestión de roles',
                'heading' => 'Gestión de roles',
            ]);
    }
}
