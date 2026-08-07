<?php

namespace App\Livewire\Admin\Workshop\AdvancePayments;

use App\Models\WorkOrderPayment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Gestión de anticipo — Taller')]
class Index extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('workshop.advance-payments.view'), 403);
    }

    public function render()
    {
        $base = WorkOrderPayment::query()->forAuthUser();

        $stats = [
            'confirmed' => (clone $base)->where('status', 'confirmed')->count(),
            'voided' => (clone $base)->where('status', 'voided')->count(),
            'total_confirmed' => (clone $base)->where('status', 'confirmed')->sum('amount'),
        ];

        return view('livewire.admin.workshop.advance-payments.index', compact('stats'));
    }
}
