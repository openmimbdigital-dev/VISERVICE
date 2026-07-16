<?php

namespace App\Actions\Workshop\Clients;

use App\Actions\LogUserHistoricalAction;
use App\Models\Client;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteClientAction
{
    use AsAction;

    public function handle(int $client_id): void
    {
        abort_unless(auth()->user()->can('workshop.clients.delete'), 403);

        $client = Client::query()->forAuthUser()->findOrFail($client_id);

        if ($client->workOrders()->exists() || $client->quotations()->exists()) {
            abort(422, 'No se puede eliminar: tiene OTs o cotizaciones asociadas.');
        }

        LogUserHistoricalAction::run(
            action: 'deleted',
            module: 'workshop.clients',
            description: "Eliminó el cliente {$client->name}",
            subject: $client,
            subject_label: $client->name,
            properties: [
                'document_type'   => $client->document_type,
                'document_number' => $client->document_number,
            ],
            business_id: (int) $client->business_id,
        );

        $client->delete();
    }
}
