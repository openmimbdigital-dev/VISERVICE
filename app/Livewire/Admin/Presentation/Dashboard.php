<?php

namespace App\Livewire\Admin\Presentation;

use App\Livewire\Concerns\BuildsDashboardModules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Inicio')]
class Dashboard extends Component
{
    use BuildsDashboardModules;

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'modules' => $this->dashboardModulesForPanel('presentation'),
        ]);
    }
}
