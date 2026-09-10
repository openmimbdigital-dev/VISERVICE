<?php

namespace App\Livewire\Admin\Presentation\Users;

use App\Support\Presentation\AccessDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Usuario')]
class Show extends Component
{
    public string $user_id = '';

    public function mount(string $user): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
        abort_unless(AccessDemoData::user($user) !== null, 404);
        $this->user_id = $user;
    }

    public function render()
    {
        return view('livewire.admin.presentation.users.show', [
            'user_record' => AccessDemoData::user($this->user_id),
        ]);
    }
}
