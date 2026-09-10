<?php

namespace App\Livewire\Auth;

use App\Actions\Subscriptions\CreateBoldPaymentLinkAction;
use App\Models\SubscriptionInvoice;
use App\Services\Bold\BoldClient;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Throwable;

/**
 * Botón para que un comercio con la cuenta sin activar pague él mismo.
 *
 * Vive dentro de la pantalla de activación pendiente: es donde cae quien se
 * registró eligiendo transferencia o efectivo, o quien intentó pagar en línea y
 * no pudo. Desde aquí puede resolverlo solo, sin esperar a que alguien le pase
 * un link.
 */
class PayPending extends Component
{
    public string $error = '';

    public function payOnline(): void
    {
        $this->error = '';

        $invoice = $this->pendingInvoice();

        if (! $invoice) {
            $this->error = 'No encontramos un cobro pendiente para tu cuenta.';

            return;
        }

        try {
            $link = CreateBoldPaymentLinkAction::run($invoice);
        } catch (ValidationException $exception) {
            $this->error = collect($exception->errors())->flatten()->first()
                ?? 'No pudimos abrir la pasarela de pago.';

            return;
        } catch (Throwable $exception) {
            report($exception);
            $this->error = 'No pudimos abrir la pasarela de pago. Inténtalo de nuevo en un momento.';

            return;
        }

        $this->redirect($link['url'], navigate: false);
    }

    /** Cobro pendiente del negocio del usuario, si lo hay. */
    private function pendingInvoice(): ?SubscriptionInvoice
    {
        $business_id = auth()->user()?->business_id;

        if (! $business_id) {
            return null;
        }

        return SubscriptionInvoice::query()
            ->with('subscription.plan')
            ->where('business_id', $business_id)
            ->where('status', 'pending')
            ->latest('id')
            ->first();
    }

    public function render()
    {
        $invoice = $this->pendingInvoice();

        return view('livewire.auth.pay-pending', [
            'invoice'      => $invoice,
            'bold_enabled' => $invoice !== null && BoldClient::make()->isConfigured(),
        ]);
    }
}
