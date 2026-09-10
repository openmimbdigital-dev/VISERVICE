<?php

namespace App\Livewire\Admin\Presentation\Billing;

use App\Support\Presentation\BillingDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Facturas')]
class InvoicesIndex extends Component
{
    public string $filter_status = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
    }

    public function render()
    {
        $invoices = collect(BillingDemoData::invoices())
            ->when($this->filter_status !== '', fn ($items) => $items->where('status', $this->filter_status))
            ->values()
            ->all();

        return view('livewire.admin.presentation.billing.invoices-index', [
            'invoices' => $invoices,
            'statuses' => BillingDemoData::invoiceStatuses(),
        ]);
    }
}
