<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Inicio')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard');
    }
}
