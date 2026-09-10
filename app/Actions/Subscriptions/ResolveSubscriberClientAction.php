<?php

namespace App\Actions\Subscriptions;

use App\Models\Business;
use App\Models\Client;
use App\Support\DianNit;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Espeja un comercio suscrito como cliente del negocio dueño de la plataforma.
 *
 * Una OT —y por lo tanto su factura— cuelga de un cliente, pero quien se
 * suscribe es un negocio. Este es el puente: por cada comercio suscrito hay un
 * cliente equivalente en el catálogo de VISERVICE, con los datos que la DIAN
 * exige del adquiriente.
 *
 * Se refresca en cada cobro para que un cambio de dirección o de razón social en
 * el comercio no deje la factura con datos viejos.
 */
class ResolveSubscriberClientAction
{
    use AsAction;

    public function handle(Business $subscriber): Client
    {
        $owner_business_id = (int) config('subscriptions.owner_business_id');

        $document_number = DianNit::normalize($subscriber->nit);

        $attributes = [
            'name'                    => (string) $subscriber->name,
            'document_type'           => $this->documentTypeFor($subscriber),
            'document_number'         => $document_number !== '' ? $document_number : null,
            'verification_digit'      => $subscriber->verification_digit,
            'person_type'             => (int) ($subscriber->person_type ?: 1),
            'fiscal_responsibilities' => (string) ($subscriber->fiscal_responsibilities ?: 'R-99-PN'),
            'address'                 => $subscriber->address,
            'city_id'                 => $subscriber->city_id,
            'email'                   => $subscriber->email,
            'phone'                   => $subscriber->phone_number,
            'status'                  => true,
        ];

        $client = $this->findExisting($owner_business_id, $document_number, $subscriber);

        if ($client) {
            $client->forceFill($attributes)->save();

            return $client;
        }

        return Client::query()->create([
            ...$attributes,
            'business_id' => $owner_business_id,
            'notes'       => "Comercio suscrito a la plataforma (negocio #{$subscriber->id}).",
            'created_by'  => auth()->id(),
        ]);
    }

    /**
     * Se busca por documento, que es lo que identifica al adquiriente ante la
     * DIAN. Si el comercio no tiene NIT se cae al nombre para no duplicar.
     */
    private function findExisting(int $owner_business_id, string $document_number, Business $subscriber): ?Client
    {
        $query = Client::query()->where('business_id', $owner_business_id);

        if ($document_number !== '') {
            return $query->where('document_number', $document_number)->first();
        }

        return $query->where('name', $subscriber->name)->first();
    }

    private function documentTypeFor(Business $subscriber): string
    {
        // 1 = persona jurídica; una empresa se identifica con NIT.
        return (int) ($subscriber->person_type ?: 1) === 1 ? 'NIT' : 'CC';
    }
}
