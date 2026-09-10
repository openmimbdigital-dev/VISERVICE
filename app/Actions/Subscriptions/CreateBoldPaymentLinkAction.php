<?php

namespace App\Actions\Subscriptions;

use App\Models\SubscriptionInvoice;
use App\Services\Bold\BoldClient;
use App\Services\Bold\BoldRequestException;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Genera el link con el que un comercio paga su suscripción en línea.
 *
 * La referencia que se le manda a Bold es la que vuelve en el webhook dentro de
 * «data.metadata.reference»: es el único hilo que une el pago con nuestro cobro,
 * así que se guarda junto al link.
 */
class CreateBoldPaymentLinkAction
{
    use AsAction;

    /** @return array{payment_link: string, url: string} */
    public function handle(SubscriptionInvoice $invoice, bool $force_new = false): array
    {
        if ($invoice->status === 'paid') {
            throw ValidationException::withMessages([
                'bold' => 'Este cobro ya está pagado.',
            ]);
        }

        // Un link vigente se reutiliza: generar uno nuevo cada vez deja links
        // sueltos por los que el comercio también podría pagar.
        if (! $force_new && $invoice->hasUsableBoldLink()) {
            return [
                'payment_link' => (string) $invoice->bold_payment_link,
                'url'          => (string) $invoice->bold_link_url,
            ];
        }

        $invoice->loadMissing(['subscription.plan', 'business']);

        $amount = round((float) $invoice->amount, 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'bold' => 'El cobro no tiene un monto por el cual generar el link.',
            ]);
        }

        $reference = $this->reference($invoice);

        try {
            $link = BoldClient::make()->createLink(
                total_amount: $amount,
                reference: $reference,
                description: $this->description($invoice),
                callback_url: $this->callbackUrl(),
                payer_email: $invoice->business?->email,
            );
        } catch (BoldRequestException $exception) {
            throw ValidationException::withMessages([
                'bold' => 'Bold no pudo generar el link: '.$exception->getMessage(),
            ]);
        }

        $invoice->forceFill([
            'bold_payment_link'    => $link['payment_link'],
            'bold_link_url'        => $link['url'],
            'bold_reference'       => $reference,
            'bold_status'          => 'ACTIVE',
            'bold_link_created_at' => now(),
        ])->save();

        return ['payment_link' => $link['payment_link'], 'url' => $link['url']];
    }

    /**
     * Referencia única del cobro. Se conserva la que ya tuviera para que un link
     * regenerado siga apuntando al mismo cobro.
     */
    private function reference(SubscriptionInvoice $invoice): string
    {
        return $invoice->bold_reference ?: $invoice->invoice_number;
    }

    private function description(SubscriptionInvoice $invoice): string
    {
        $plan = $invoice->subscription?->plan?->name;

        return $plan
            ? "Suscripción {$plan} · {$invoice->invoice_number}"
            : "Suscripción {$invoice->invoice_number}";
    }

    private function callbackUrl(): ?string
    {
        $route = (string) config('bold.link.callback_route');

        if ($route === '' || ! app('router')->has($route)) {
            return null;
        }

        $url = route($route);

        // Bold solo acepta https. En local no se manda y el pagador se queda en
        // el checkout, que es preferible a que la creación del link falle.
        return str_starts_with($url, 'https://') ? $url : null;
    }
}
