<?php

namespace App\Livewire\Admin\Payments;

use App\Models\BankAccount;
use App\Models\SubscriptionInvoice;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Pagos Pendientes')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showConfirmModal = false;
    public ?int $selected_invoice_id = null;
    public string $confirm_payment_method = '';
    public ?int $bank_account_id = null;
    public string $admin_notes = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openConfirm(int $invoice_id): void
    {
        $invoice = SubscriptionInvoice::findOrFail($invoice_id);

        $this->selected_invoice_id    = $invoice->id;
        $this->confirm_payment_method = $invoice->payment_method ?? '';
        $this->bank_account_id        = null;
        $this->admin_notes            = '';
        $this->showConfirmModal       = true;
    }

    public function confirmPayment(): void
    {
        $invoice = SubscriptionInvoice::with('subscription')->findOrFail($this->selected_invoice_id);

        $invoice->update([
            'status'          => 'paid',
            'paid_at'         => now(),
            'bank_account_id' => $this->bank_account_id ?: null,
            'notes'           => $this->admin_notes ?: null,
        ]);

        $invoice->subscription->update(['status' => 'active']);

        $this->closeModal();
        $this->dispatch('swal', ['title' => '¡Pago confirmado! La suscripción está activa.', 'icon' => 'success']);
    }

    public function rejectPayment(int $invoice_id): void
    {
        $invoice = SubscriptionInvoice::with('subscription')->findOrFail($invoice_id);

        $invoice->update(['status' => 'failed']);
        $invoice->subscription->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancellation_reason' => 'Pago rechazado por administrador',
        ]);

        $this->dispatch('swal', ['title' => 'Pago rechazado. La suscripción fue cancelada.', 'icon' => 'warning']);
    }

    public function closeModal(): void
    {
        $this->showConfirmModal       = false;
        $this->selected_invoice_id    = null;
        $this->confirm_payment_method = '';
        $this->bank_account_id        = null;
        $this->admin_notes            = '';
    }

    public function getProofUrl(?string $proofPath): ?string
    {
        return $proofPath ? Storage::disk('public')->url($proofPath) : null;
    }

    public function render()
    {
        $invoices = SubscriptionInvoice::with(['subscription.plan', 'business'])
            ->where('status', 'pending')
            ->whereHas('subscription', fn ($q) => $q->where('status', 'pending'))
            ->when($this->search, fn ($q) => $q->whereHas('business', fn ($b) =>
                $b->where('name', 'like', "%{$this->search}%")
            ))
            ->latest()
            ->paginate(10);

        $bankAccounts = BankAccount::with('bank')
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $stats = [
            'pending'          => SubscriptionInvoice::where('status', 'pending')
                                    ->whereHas('subscription', fn ($q) => $q->where('status', 'pending'))
                                    ->count(),
            'confirmed_today'  => SubscriptionInvoice::where('status', 'paid')
                                    ->whereDate('paid_at', today())
                                    ->count(),
        ];

        return view('livewire.admin.payments.index', compact('invoices', 'bankAccounts', 'stats'));
    }
}
