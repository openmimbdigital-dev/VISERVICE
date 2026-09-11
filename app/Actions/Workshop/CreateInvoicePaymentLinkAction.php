<?php

namespace App\Actions\Workshop;

use App\Models\BusinessBoldSetting;
use App\Models\WorkOrderInvoice;
use App\Services\Bold\BoldClient;
use App\Services\Bold\BoldRequestException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Genera el link con el que el cliente del taller paga su factura.
 *
 * El cobro sale con las llaves de Bold del negocio, para que el dinero entre a su
 * cuenta. Si no las tiene configuradas se usan las de la plataforma como
 * respaldo: nadie se queda sin poder cobrar, pero ese dinero entra a nuestra
 * cuenta y después hay que girárselo, así que en qué cuenta salió queda guardado
 * en el cobro y a la vista en la pantalla.
 */
class CreateInvoicePaymentLinkAction
{
    use AsAction;

    /** @return array{payment_link: string, url: string, account: string} */
    public function handle(WorkOrderInvoice $invoice, bool $force_new = false): array
    {
        abort_unless(auth()->user()?->can('workshop.invoices.view'), 403);

        $invoice = WorkOrderInvoice::query()->forAuthUser()->with('workOrder.client')->findOrFail($invoice->id);

        if ($invoice->status === 'pagada') {
            throw ValidationException::withMessages(['bold' => 'Esta factura ya está pagada.']);
        }

        if ($invoice->status === 'anulada') {
            throw ValidationException::withMessages(['bold' => 'Esta factura está anulada.']);
        }

        $amount = round((float) $invoice->total, 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages(['bold' => 'La factura no tiene un monto por el cual cobrar.']);
        }

        // Un link vigente se reutiliza: generar uno nuevo cada vez deja links
        // sueltos por los que también se podría pagar.
        if (! $force_new && $invoice->hasUsableBoldLink()) {
            return [
                'payment_link' => (string) $invoice->bold_payment_link,
                'url'          => (string) $invoice->bold_link_url,
                'account'      => (string) $invoice->bold_account,
            ];
        }

        $setting = BusinessBoldSetting::query()->where('business_id', $invoice->business_id)->first();
        $client = BoldClient::forBusiness($setting);

        if (! $client->isConfigured()) {
            throw ValidationException::withMessages([
                'bold' => 'No hay una cuenta de Bold configurada para cobrar esta factura.',
            ]);
        }

        $reference = $invoice->reference.'-'.Str::upper(Str::random(6));

        try {
            $link = $client->createLink(
                total_amount: $amount,
                reference: $reference,
                description: $this->description($invoice),
                callback_url: $this->callbackUrl($reference),
                payer_email: $invoice->workOrder?->client?->email,
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
            'bold_account'         => $client->account(),
            'bold_link_created_at' => now(),
        ])->save();

        return [
            'payment_link' => $link['payment_link'],
            'url'          => $link['url'],
            'account'      => $client->account(),
        ];
    }

    private function description(WorkOrderInvoice $invoice): string
    {
        $business = $invoice->business?->name;

        return $business
            ? "Factura {$invoice->reference} · {$business}"
            : "Factura {$invoice->reference}";
    }

    private function callbackUrl(string $reference): ?string
    {
        $route = (string) config('bold.link.callback_route');

        if ($route === '' || ! app('router')->has($route)) {
            return null;
        }

        $url = route($route, ['ref' => $reference]);

        // Bold solo acepta https. En local no se manda y el pagador se queda en
        // el checkout, que es preferible a que la creación del link falle.
        return str_starts_with($url, 'https://') ? $url : null;
    }
}
