<?php

namespace App\Livewire\Admin\Subscriptions;

use App\Actions\Subscriptions\ConfirmSubscriptionPaymentAction;
use App\Actions\Subscriptions\CreateBoldPaymentLinkAction;
use App\Models\Business;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;
use App\Support\ConfirmationAlert;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;

#[Layout('layouts.app')]
#[Title('Gestión de Suscripciones')]
class Index extends Component
{
    use LivewireAlert;

    /** Registro en espera de que confirmen la acción. */
    public ?int $cancelling_subscription_id = null;

    use WithPagination;

    public string $search = '';
    public string $filter_status = '';
    public bool $showModal = false;
    public bool $showInvoiceModal = false;
    public ?int $selected_subscription_id = null;

    // Formulario nueva suscripción
    public ?int $business_id = null;
    public ?int $subscription_plan_id = null;
    public string $billing_cycle = 'monthly';
    public string $started_at = '';
    public bool $is_trial = false;
    public int $trial_days = 15;
    public bool $auto_renew = true;
    public string $notes = '';

    // Formulario registrar pago
    public string $payment_method = '';
    public string $payment_reference = '';
    public string $paid_at = '';
    public string $invoice_notes = '';

    protected function rules(): array
    {
        return [
            'business_id'          => 'required|exists:businesses,id',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle'        => 'required|in:monthly,quarterly,semiannual,annual',
            'started_at'           => 'required|date',
            'is_trial'             => 'boolean',
            'trial_days'           => 'required_if:is_trial,true|integer|min:1|max:90',
            'auto_renew'           => 'boolean',
            'notes'                => 'nullable|string',
        ];
    }

    public bool $showPaymentLinkModal = false;

    public bool $showPaymentsModal = false;

    public ?int $payments_subscription_id = null;

    public string $payment_link_url = '';

    public string $payment_link_invoice = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->started_at = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $sub = Subscription::findOrFail($id);
        $this->selected_subscription_id = $sub->id;
        $this->business_id = $sub->business_id;
        $this->subscription_plan_id = $sub->subscription_plan_id;
        $this->billing_cycle = $sub->billing_cycle;
        $this->started_at = $sub->started_at->format('Y-m-d');
        $this->is_trial = $sub->status === 'trial';
        $this->auto_renew = $sub->auto_renew;
        $this->notes = $sub->notes ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $plan = SubscriptionPlan::findOrFail($this->subscription_plan_id);
        $priceData = $plan->getPriceForCycle($this->billing_cycle);
        $startedAt = \Carbon\Carbon::parse($this->started_at);
        $endsAt = $startedAt->copy()->addMonths($priceData['months']);

        $status = $this->is_trial ? 'trial' : 'active';
        $trialEndsAt = $this->is_trial
            ? $startedAt->copy()->addDays($this->trial_days)
            : null;

        $data = [
            'business_id'          => $this->business_id,
            'subscription_plan_id' => $this->subscription_plan_id,
            'status'               => $status,
            'billing_cycle'        => $this->billing_cycle,
            'monthly_price'        => $plan->monthly_price,
            'total_price'          => $priceData['total'],
            'discount_percentage'  => $priceData['discount'],
            'started_at'           => $startedAt->toDateString(),
            'ends_at'              => $endsAt->toDateString(),
            'trial_ends_at'        => $trialEndsAt?->toDateString(),
            'auto_renew'           => $this->auto_renew,
            'notes'                => $this->notes ?: null,
            'created_by'           => auth()->id(),
        ];

        if ($this->selected_subscription_id) {
            Subscription::findOrFail($this->selected_subscription_id)->update($data);
            $this->dispatch('swal', ['title' => 'Suscripción actualizada', 'icon' => 'success']);
        } else {
            $sub = Subscription::create($data);
            // Crear factura automáticamente si no es trial
            if (! $this->is_trial) {
                SubscriptionInvoice::create([
                    'subscription_id'      => $sub->id,
                    'business_id'          => $sub->business_id,
                    'invoice_number'       => SubscriptionInvoice::generateInvoiceNumber(),
                    'amount'               => $sub->total_price,
                    'status'               => 'pending',
                    'billing_period_start' => $sub->started_at->toDateString(),
                    'billing_period_end'   => $sub->ends_at->toDateString(),
                    'due_date'             => $sub->started_at->toDateString(),
                    'created_by'           => auth()->id(),
                ]);
            }
            $this->dispatch('swal', ['title' => 'Suscripción creada', 'icon' => 'success']);
        }

        $this->closeModal();
    }

    public function cancel(int $id): void
    {
        // Pregunta con el diálogo del proyecto; la acción va en cancelConfirmed().
        $this->cancelling_subscription_id = $id;

        $this->confirm('¿Cancelar esta suscripción?', ConfirmationAlert::options(
                on_confirmed: 'subscription-cancel-confirmed',
                confirm_text: 'Cancelar suscripción',
                icon: 'warning',
                text: 'El comercio perderá el acceso al terminar el período pagado.',
            ));
    }

    #[On('subscription-cancel-confirmed')]
    public function cancelConfirmed(): void
    {
        $id = $this->cancelling_subscription_id;
        $this->cancelling_subscription_id = null;

        if (! $id) {
            return;
        }

        Subscription::findOrFail($id)->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);
        $this->dispatch('swal', ['title' => 'Suscripción cancelada', 'icon' => 'warning']);

    }

    public function openInvoiceModal(int $subscriptionId): void
    {
        $this->selected_subscription_id = $subscriptionId;
        $this->paid_at = now()->format('Y-m-d');
        $this->showInvoiceModal = true;
    }

    public function registerPayment(): void
    {
        $this->validate([
            'payment_method'    => 'required|string|max:100',
            'payment_reference' => 'nullable|string|max:100',
            'paid_at'           => 'required|date',
        ]);

        $sub = Subscription::with('invoices')->findOrFail($this->selected_subscription_id);
        $pendingInvoice = $sub->invoices()->where('status', 'pending')->latest()->first();

        // Confirmar el cobro activa la suscripción y genera la OT con su factura.
        // Es el mismo camino que usa el webhook de Bold cuando el pago es en línea.
        // Si la facturación falla, el pago ya quedó registrado: no se pierde.
        $notice = null;

        if ($pendingInvoice) {
            try {
                $invoice = ConfirmSubscriptionPaymentAction::run(
                    invoice: $pendingInvoice,
                    payment_method: $this->payment_method,
                    payment_reference: $this->payment_reference ?: null,
                    paid_at: $this->paid_at,
                    notes: $this->invoice_notes ?: null,
                );

                $notice = $invoice ? "Se generó la factura {$invoice->reference}." : null;
            } catch (ValidationException $exception) {
                $notice = collect($exception->errors())->flatten()->first();
            } catch (Throwable $exception) {
                report($exception);
                $notice = 'No se pudo generar la factura de este cobro.';
            }
        }

        $this->closeInvoiceModal();
        $this->dispatch('swal', [
            'title' => 'Pago registrado',
            'text'  => $notice,
            'icon'  => 'success',
        ]);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    /**
     * Genera —o recupera— el link con el que el comercio paga en línea.
     *
     * El link se guarda en el cobro pendiente: cuando Bold avise que se pagó, el
     * webhook lo encuentra por la referencia y confirma todo solo.
     */
    public function generatePaymentLink(int $subscriptionId): void
    {
        $subscription = Subscription::query()->findOrFail($subscriptionId);
        $pending = $subscription->invoices()->where('status', 'pending')->latest()->first();

        if (! $pending) {
            $this->dispatch('swal', [
                'title' => 'Sin cobro pendiente',
                'text'  => 'Esta suscripción no tiene un cobro por el cual generar el link.',
                'icon'  => 'info',
            ]);

            return;
        }

        try {
            $link = CreateBoldPaymentLinkAction::run($pending);
        } catch (ValidationException $exception) {
            $this->dispatch('swal', [
                'title' => 'No se pudo generar el link',
                'text'  => collect($exception->errors())->flatten()->first(),
                'icon'  => 'error',
            ]);

            return;
        } catch (Throwable $exception) {
            report($exception);
            $this->dispatch('swal', [
                'title' => 'No se pudo generar el link',
                'text'  => 'Revisa la configuración de Bold e inténtalo de nuevo.',
                'icon'  => 'error',
            ]);

            return;
        }

        $this->payment_link_url = $link['url'];
        $this->payment_link_invoice = $pending->invoice_number;
        $this->showPaymentLinkModal = true;
    }

    /** Detalle de cómo pagó el comercio: medio, códigos de la pasarela y montos. */
    public function viewPayments(int $subscriptionId): void
    {
        $this->payments_subscription_id = $subscriptionId;
        $this->showPaymentsModal = true;
    }

    public function closePaymentsModal(): void
    {
        $this->showPaymentsModal = false;
        $this->payments_subscription_id = null;
    }

    public function closePaymentLinkModal(): void
    {
        $this->showPaymentLinkModal = false;
        $this->payment_link_url = '';
        $this->payment_link_invoice = '';
    }

    public function closeInvoiceModal(): void
    {
        $this->showInvoiceModal = false;
        $this->selected_subscription_id = null;
        $this->payment_method = '';
        $this->payment_reference = '';
        $this->paid_at = '';
        $this->invoice_notes = '';
        $this->resetValidation();
    }

    private function resetForm(): void
    {
        $this->selected_subscription_id = null;
        $this->business_id = null;
        $this->subscription_plan_id = null;
        $this->billing_cycle = 'monthly';
        $this->started_at = '';
        $this->is_trial = false;
        $this->trial_days = 15;
        $this->auto_renew = true;
        $this->notes = '';
        $this->resetValidation();
    }

    public function render()
    {
        $subscriptions = Subscription::with(['business', 'plan'])
            ->when($this->search, fn($q) => $q->whereHas('business', fn($b) => $b->where('name', 'like', "%{$this->search}%")))
            ->when($this->filter_status, fn($q) => $q->where('status', $this->filter_status))
            ->latest()
            ->paginate(15);

        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
        $businesses = Business::where('status', true)->orderBy('name')->get();

        // Stats para el superadmin
        $stats = [
            'active'    => Subscription::whereIn('status', ['active'])->count(),
            'trial'     => Subscription::where('status', 'trial')->count(),
            'expired'   => Subscription::whereIn('status', ['expired', 'past_due'])->count(),
            'cancelled' => Subscription::where('status', 'cancelled')->count(),
        ];

        $payments = $this->payments_subscription_id
            ? SubscriptionPayment::query()
                ->with('createdBy')
                ->where('subscription_id', $this->payments_subscription_id)
                ->orderByDesc('paid_at')
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('livewire.admin.subscriptions.index',
            compact('subscriptions', 'plans', 'businesses', 'stats', 'payments'));
    }
}
