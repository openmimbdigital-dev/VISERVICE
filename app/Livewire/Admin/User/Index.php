<?php

namespace App\Livewire\Admin\User;

use App\Models\User;
use Livewire\Component;

class Index extends Component
{
    public int $usersTotal = 0;

    public int $usersThisMonth = 0;

    public function mount(): void
    {
        $this->usersTotal = User::query()->count();
        $this->usersThisMonth = User::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    public function render()
    {
        return view('livewire.admin.user.index')
            ->layout('layouts.app', [
                'title' => 'Gestión de Usuarios',
                'heading' => 'Gestión de Usuarios',
            ]);
    }
}
