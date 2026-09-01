<?php

namespace App\Livewire\Admin\Workshop\Remissions;

use App\Enums\WorkOrderStatus;
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
        $base = Remission::query()->forAuthUser();

        $stats = [
            'creada' => (clone $base)->where('status', WorkOrderStatus::Created)->count(),
            'en_proceso' => (clone $base)->where('status', WorkOrderStatus::InProgress)->count(),
            'finalizada' => (clone $base)->where('status', WorkOrderStatus::Completed)->count(),
            'cancelada' => (clone $base)->where('status', WorkOrderStatus::Cancelled)->count(),
        ];

        return view('livewire.admin.workshop.remissions.index', compact('stats'));
    }
}
