<?php

namespace App\Livewire\Admin\Presentation\Roles;

use App\Support\Presentation\AccessDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Rol')]
class Show extends Component
{
    public string $role_id = '';

    public function mount(string $role): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
        abort_unless(AccessDemoData::role($role) !== null, 404);
        $this->role_id = $role;
    }

    public function render()
    {
        return view('livewire.admin.presentation.roles.show', [
            'role_record' => AccessDemoData::role($this->role_id),
        ]);
    }
}
