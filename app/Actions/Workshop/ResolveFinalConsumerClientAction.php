<?php

namespace App\Actions\Workshop;

use App\Models\Client;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Cliente «Consumidor final» del negocio, creándolo la primera vez que hace falta.
 *
 * Una OT siempre apunta a un cliente: de él cuelgan los equipos, las remisiones y
 * la factura. Cuando quien trae el equipo no quiere que la factura salga a su
 * nombre, en vez de dejar la OT sin dueño se usa este cliente reservado, que lleva
 * los datos que la DIAN define para un adquiriente no identificado.
 *
 * Se reconoce por su número de documento (222222222222), que la DIAN reserva
 * justamente para esto, así que no hace falta una columna extra para marcarlo.
 */
class ResolveFinalConsumerClientAction
{
    use AsAction;

    public function handle(int $business_id): Client
    {
        $consumer = (array) config('dian.final_consumer');
        $document_number = (string) $consumer['document_number'];

        $client = Client::query()
            ->where('business_id', $business_id)
            ->where('document_number', $document_number)
            ->first();

        if ($client) {
            // Si alguien lo desactivó, se reactiva: la OT lo necesita seleccionable.
            if (! $client->status) {
                $client->forceFill(['status' => true])->save();
            }

            return $client;
        }

        return Client::query()->create([
            'business_id'             => $business_id,
            'name'                    => (string) $consumer['name'],
            'document_type'           => 'CC',
            'document_number'         => $document_number,
            'person_type'             => (int) $consumer['person_type'],
            'fiscal_responsibilities' => (string) $consumer['tax_level_code'],
            'status'                  => true,
            'notes'                   => 'Cliente reservado para facturar a consumidor final ante la DIAN. '
                .'No lo elimines: las OT que no se facturan a nombre del cliente apuntan aquí.',
            'created_by'              => auth()->id(),
        ]);
    }

    /** ¿Este cliente es el reservado para consumidor final? */
    public static function isFinalConsumer(?Client $client): bool
    {
        return $client !== null
            && (string) $client->document_number === (string) config('dian.final_consumer.document_number');
    }
}
