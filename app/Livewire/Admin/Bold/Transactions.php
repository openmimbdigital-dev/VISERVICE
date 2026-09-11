<?php

namespace App\Livewire\Admin\Bold;

use App\Models\BoldStatusCheck;
use App\Models\BoldWebhookEvent;
use App\Models\Business;
use App\Models\SubscriptionPayment;
use App\Models\WorkOrderInvoice;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Todo lo que ha cobrado Bold a través de la plataforma, en un solo sitio.
 *
 * El dato vive repartido en dos lados que nunca se habían mirado juntos: las
 * suscripciones que nos pagan los negocios y las facturas que les pagan sus
 * clientes. Son cobros distintos —uno es ingreso nuestro, el otro es dinero
 * ajeno— y justo por eso conviene verlos en la misma lista: lo que entra por la
 * cuenta de la plataforma sin ser nuestro es plata que hay que girar, y hasta
 * ahora no había dónde verlo.
 *
 * Cada fila trae de dónde salió —qué factura, qué orden, qué negocio—, con qué se
 * pagó y a qué cuenta entró.
 */
#[Layout('layouts.app')]
#[Title('Transacciones de Bold')]
class Transactions extends Component
{
    public string $from = '';

    public string $to = '';

    public string $type = '';

    public string $account = '';

    public ?int $business_id = null;

    public ?string $detail_key = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);

        $this->from = now()->startOfMonth()->toDateString();
        $this->to = now()->toDateString();
    }

    public function resetFilters(): void
    {
        $this->from = now()->startOfMonth()->toDateString();
        $this->to = now()->toDateString();
        $this->type = '';
        $this->account = '';
        $this->business_id = null;
    }

    /** Abre el detalle de una transacción, o lo cierra si ya estaba abierto. */
    public function toggleDetail(string $key): void
    {
        $this->detail_key = $this->detail_key === $key ? null : $key;
    }

    /**
     * Las dos fuentes, con la misma forma, ordenadas de la más reciente.
     *
     * Se unen en memoria y no con SQL porque son tablas con poco en común —una
     * factura de taller y un cobro de suscripción no comparten ni columnas ni
     * significado—, y el volumen de cobros en línea no justifica retorcer una
     * consulta para ahorrarse un ordenamiento.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function transactions(): Collection
    {
        return $this->subscriptionRows()
            ->concat($this->invoiceRows())
            ->filter(fn (array $row) => $this->matchesFilters($row))
            ->sortByDesc('paid_at')
            ->values();
    }

    /** @return Collection<int, array<string, mixed>> */
    private function subscriptionRows(): Collection
    {
        return SubscriptionPayment::query()
            ->where('gateway', 'bold')
            ->with(['business', 'invoice', 'subscription.plan'])
            ->get()
            ->map(fn (SubscriptionPayment $payment) => [
                'key'            => 'sub-'.$payment->id,
                'type'           => 'subscription',
                'type_label'     => 'Suscripción',
                'paid_at'        => $payment->paid_at ?? $payment->created_at,
                'business'       => $payment->business?->name ?? '—',
                'business_id'    => (int) $payment->business_id,
                'concept'        => $payment->subscription?->plan?->name ?? 'Suscripción',
                'document'       => $payment->invoice?->invoice_number ?? '—',
                'document_url'   => null,
                'related'        => null,
                'related_url'    => null,
                'amount'         => (float) $payment->amount,
                'currency'       => (string) $payment->currency,
                'method'         => $payment->methodLabel(),
                'transaction_id' => $payment->gateway_payment_id,
                'reference'      => $payment->gateway_reference,
                // Las suscripciones se cobran siempre con nuestras llaves: ese
                // dinero es nuestro y no hay nada que girar.
                'account'        => 'platform',
                'own_money'      => true,
            ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function invoiceRows(): Collection
    {
        return WorkOrderInvoice::query()
            ->withoutGlobalScopes()
            ->whereNotNull('bold_payment_id')
            ->with(['business', 'workOrder'])
            ->get()
            ->map(fn (WorkOrderInvoice $invoice) => [
                'key'            => 'inv-'.$invoice->id,
                'type'           => 'invoice',
                'type_label'     => 'Factura de taller',
                'paid_at'        => $invoice->paid_at ?? $invoice->updated_at,
                'business'       => $invoice->business?->name ?? '—',
                'business_id'    => (int) $invoice->business_id,
                'concept'        => 'Factura del taller a su cliente',
                'document'       => $invoice->reference,
                'document_url'   => route('admin.workshop.invoices.show', $invoice->id),
                'related'        => $invoice->workOrder?->reference,
                'related_url'    => $invoice->work_order_id
                    ? route('admin.workshop.work-orders.show', $invoice->work_order_id)
                    : null,
                'amount'         => (float) $invoice->total,
                'currency'       => 'COP',
                'method'         => $invoice->bold_payment_method ?: 'En línea',
                'transaction_id' => $invoice->bold_payment_id,
                'reference'      => $invoice->bold_reference,
                'account'        => $invoice->bold_account ?: 'platform',
                // Dinero del taller: si entró por nuestra cuenta, hay que girarlo.
                'own_money'      => false,
            ]);
    }

    /** @param  array<string, mixed>  $row */
    private function matchesFilters(array $row): bool
    {
        if ($this->from !== '' && $row['paid_at'] && $row['paid_at']->lt($this->from.' 00:00:00')) {
            return false;
        }

        if ($this->to !== '' && $row['paid_at'] && $row['paid_at']->gt($this->to.' 23:59:59')) {
            return false;
        }

        if ($this->type !== '' && $row['type'] !== $this->type) {
            return false;
        }

        if ($this->account !== '' && $row['account'] !== $this->account) {
            return false;
        }

        return ! ($this->business_id && $row['business_id'] !== (int) $this->business_id);
    }

    /**
     * Lo que quedó registrado de esa transacción: el aviso de Bold y las
     * consultas que se le hicieron. Es el respaldo de por qué se dio por pagada.
     *
     * @return array{events: Collection<int, BoldWebhookEvent>, checks: Collection<int, BoldStatusCheck>}
     */
    private function detail(): array
    {
        $row = $this->transactions()->firstWhere('key', $this->detail_key);

        if (! $row) {
            return ['events' => collect(), 'checks' => collect()];
        }

        return [
            'events' => BoldWebhookEvent::query()
                ->when($row['reference'], fn ($q, $reference) => $q->where('reference', $reference))
                ->when($row['transaction_id'], fn ($q, $id) => $q->orWhere('payment_id', $id))
                ->latest('id')
                ->limit(10)
                ->get(),
            'checks' => BoldStatusCheck::query()
                ->when($row['reference'], fn ($q, $reference) => $q->where('reference', $reference))
                ->latest('id')
                ->limit(10)
                ->get(),
        ];
    }

    public function render()
    {
        $transactions = $this->transactions();

        return view('livewire.admin.bold.transactions', [
            'transactions' => $transactions,
            'businesses'   => Business::query()->orderBy('name')->get(['id', 'name']),
            'totals'       => [
                'count'      => $transactions->count(),
                'amount'     => $transactions->sum('amount'),
                'ours'       => $transactions->where('own_money', true)->sum('amount'),
                // Lo que entró por nuestra cuenta sin ser nuestro: hay que girarlo.
                'to_settle'  => $transactions
                    ->where('own_money', false)
                    ->where('account', 'platform')
                    ->sum('amount'),
                'businesses' => $transactions->where('account', 'business')->sum('amount'),
            ],
            'detail' => $this->detail_key ? $this->detail() : null,
        ]);
    }
}
