<?php

namespace App\Livewire\Admin\Workshop\Remissions;

use App\Models\Remission;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Remisiones')]
class Index extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('workshop.remissions.view'), 403);
    }

    #[On('remission-deleted')]
    public function onRecordDeleted(): void {}

    public function render()
    {
        $stats = [
            'borrador'  => Remission::query()->forAuthUser()->where('status', 'borrador')->count(),
            'emitida'   => Remission::query()->forAuthUser()->where('status', 'emitida')->count(),
            'entregada' => Remission::query()->forAuthUser()->where('status', 'entregada')->count(),
        ];

        return view('livewire.admin.workshop.remissions.index', compact('stats'));
    }
}
