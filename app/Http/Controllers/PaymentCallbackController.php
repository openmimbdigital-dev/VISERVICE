<?php

namespace App\Http\Controllers;

use App\Actions\Subscriptions\SyncBoldPaymentStatusAction;
use App\Models\SubscriptionInvoice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Pantalla a la que vuelve el comercio cuando termina de pagar en Bold.
 *
 * Antes solo decía «estamos confirmando» y se quedaba esperando el webhook. Aquí
 * se aprovecha que el pagador está de vuelta —con su referencia en la URL— para
 * preguntarle a Bold en qué quedó el cobro y acreditarlo en el acto si ya está
 * pagado. Confirmar por este camino o por el webhook lleva a la misma acción, así
 * que el que llegue segundo no repite nada.
 *
 * Si Bold todavía lo está procesando, la pantalla se recarga sola unas cuantas
 * veces antes de dejarlo en «te avisamos»: PSE puede tardar.
 */
class PaymentCallbackController extends Controller
{
    /** Cuántas recargas automáticas antes de dejar de insistir. */
    private const MAX_TRIES = 5;

    public function __invoke(Request $request): View
    {
        $reference = trim((string) $request->query('ref', ''));
        $try = max(0, (int) $request->query('try', 0));

        $invoice = SubscriptionInvoice::findByBoldReference($reference);

        $state = $this->state($invoice);

        return view('payments.callback', [
            'state'     => $state,
            'invoice'   => $invoice,
            'retry_url' => $state === 'en_proceso' && $try < self::MAX_TRIES
                ? route('subscriptions.payment.callback', ['ref' => $reference, 'try' => $try + 1])
                : null,
        ]);
    }

    /** pagado | en_proceso | desconocido */
    private function state(?SubscriptionInvoice $invoice): string
    {
        if (! $invoice) {
            return 'desconocido';
        }

        if ($invoice->status === 'paid') {
            return 'pagado';
        }

        $result = SyncBoldPaymentStatusAction::run($invoice);

        if ($result['changed'] || $invoice->refresh()->status === 'paid') {
            return 'pagado';
        }

        return $result['status'] === 'ERROR' ? 'desconocido' : 'en_proceso';
    }
}
