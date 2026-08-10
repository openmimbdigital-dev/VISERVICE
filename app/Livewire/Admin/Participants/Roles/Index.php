<?php

namespace App\Livewire\Admin\Participants\Roles;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Roles de participantes')]
class Index extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('participants.roles.view'), 403);
    }

    public function render()
    {
        return view('livewire.admin.participants.roles.index');
    }
}
