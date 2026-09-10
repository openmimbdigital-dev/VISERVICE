<?php

namespace App\Livewire\Admin\Presentation\Billing;

use App\Support\Presentation\BillingDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Pagos')]
class PaymentsIndex extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
    }

    public function render()
    {
        return view('livewire.admin.presentation.billing.payments-index', [
            'payments' => BillingDemoData::payments(),
        ]);
    }
}
