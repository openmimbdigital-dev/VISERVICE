<?php

namespace App\Livewire\Admin\Presentation\Billing;

use App\Support\Presentation\BillingDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Facturación')]
class Index extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
    }

    public function render()
    {
        $invoices = BillingDemoData::invoices();

        return view('livewire.admin.presentation.billing.index', [
            'stats' => [
                'invoices' => count($invoices),
                'paid' => collect($invoices)->where('status', 'paid')->count(),
                'pending' => collect($invoices)->whereIn('status', ['issued', 'overdue'])->count(),
                'concepts' => count(BillingDemoData::concepts()),
            ],
        ]);
    }
}
