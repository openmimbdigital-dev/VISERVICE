<?php

namespace App\Livewire\Admin\Workshop\Clients;

use App\Models\Client;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Clientes')]
class Index extends Component
{
    #[On('client-deleted')]
    public function onRecordDeleted(): void {}

    public function render()
    {
        $stats = [
            'total'  => Client::forAuthUser()->count(),
            'active' => Client::forAuthUser()->where('status', true)->count(),
        ];

        return view('livewire.admin.workshop.clients.index', compact('stats'));
    }
}
