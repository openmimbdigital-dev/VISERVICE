<?php

namespace App\Livewire\Admin\Workshop\Invoices;

use App\Models\WorkOrderInvoice;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Facturación')]
class Index extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('workshop.invoices.view'), 403);
    }

    public function render()
    {
        $base = WorkOrderInvoice::query()->forAuthUser();

        $stats = [
            'total'     => (clone $base)->count(),
            'pendiente' => (clone $base)->where('status', 'pendiente')->count(),
            'pagada'    => (clone $base)->where('status', 'pagada')->count(),
            'vencida'   => (clone $base)->where('status', 'vencida')->count(),
        ];

        return view('livewire.admin.workshop.invoices.index', compact('stats'));
    }
}
