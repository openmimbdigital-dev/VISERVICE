<?php

namespace App\Livewire\Admin\Presentation\Billing;

use App\Support\Presentation\BillingDemoData;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Presentación — Factura')]
class InvoiceShow extends Component
{
    public string $invoice_id = '';

    public bool $showPaymentModal = false;

    public string $payment_amount = '';

    public string $payment_method = 'Transferencia';

    public string $payment_reference = '';

    public function mount(string $invoice): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);
        abort_unless(BillingDemoData::invoice($invoice) !== null, 404);
        $this->invoice_id = $invoice;
    }

    public function openPayment(): void
    {
        $invoice = BillingDemoData::invoice($this->invoice_id);
        $this->payment_amount = (string) ($invoice['amount'] ?? 0);
        $this->payment_method = 'Transferencia';
        $this->payment_reference = '';
        $this->showPaymentModal = true;
        $this->resetValidation();
    }

    public function closePayment(): void
    {
        $this->showPaymentModal = false;
    }

    public function savePayment(): void
    {
        $this->validate([
            'payment_amount' => 'required|integer|min:1',
            'payment_method' => 'required|string|max:40',
            'payment_reference' => 'nullable|string|max:40',
        ], [
            'payment_amount.required' => 'El valor del pago es obligatorio.',
        ]);

        $invoice = BillingDemoData::invoice($this->invoice_id);
        abort_unless($invoice !== null, 404);

        BillingDemoData::addPayment([
            'id' => 'pay-custom-'.uniqid(),
            'invoice_number' => $invoice['number'],
            'student' => $invoice['student'],
            'amount' => (int) $this->payment_amount,
            'method' => $this->payment_method,
            'paid_at' => now()->toDateString(),
            'reference' => trim($this->payment_reference) !== '' ? trim($this->payment_reference) : 'DEMO-'.random_int(100, 999),
        ]);

        $this->closePayment();
        $this->dispatch('swal', ['title' => 'Pago registrado', 'icon' => 'success']);
    }

    public function render()
    {
        $invoice = BillingDemoData::invoice($this->invoice_id);
        $payments = collect(BillingDemoData::payments())
            ->where('invoice_number', $invoice['number'] ?? '')
            ->values()
            ->all();

        return view('livewire.admin.presentation.billing.invoice-show', [
            'invoice_record' => $invoice,
            'payments' => $payments,
        ]);
    }
}
