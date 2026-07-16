<?php

namespace App\Actions\Workshop\Clients;

use App\Actions\LogUserHistoricalAction;
use App\Models\Client;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateClientAction
{
    use AsAction;

    /**
     * Crea o actualiza un cliente del taller.
     *
     * @param  array  $data  Datos ya validados del cliente
     */
    public function handle(int $business_id, ?int $client_id, array $data): Client
    {
        abort_unless(
            auth()->user()->can($client_id ? 'workshop.clients.edit' : 'workshop.clients.create'),
            403
        );

        $user = auth()->user();

        if (! $user->hasRole('superAdmin')) {
            abort_unless($user->belongsToBusiness($business_id), 403);
        }

        $attributes = [
            'business_id'     => $business_id,
            'city_id'         => $data['city_id'] ?? null,
            'name'            => $data['name'],
            'document_type'   => $data['document_type'],
            'document_number' => $data['document_number'],
            'phone'           => $data['phone'],
            'email'           => $data['email'],
            'address'         => $data['address'],
            'contact_name'    => $data['contact_name'],
            'status'          => $data['status'],
            'notes'           => $data['notes'],
        ];

        if ($client_id) {
            $client = Client::query()->forAuthUser()->findOrFail($client_id);
            abort_unless((int) $client->business_id === (int) $business_id, 403);

            $client->update($attributes);
            $client = $client->fresh();

            LogUserHistoricalAction::run(
                action: 'updated',
                module: 'workshop.clients',
                description: "Actualizó el cliente {$client->name}",
                subject: $client,
                subject_label: $client->name,
                properties: [
                    'document_type'   => $client->document_type,
                    'document_number' => $client->document_number,
                    'status'          => $client->status,
                ],
                business_id: $business_id,
            );

            return $client;
        }

        $attributes['created_by'] = auth()->id();

        $client = Client::create($attributes);

        LogUserHistoricalAction::run(
            action: 'created',
            module: 'workshop.clients',
            description: "Creó el cliente {$client->name}",
            subject: $client,
            subject_label: $client->name,
            properties: [
                'document_type'   => $client->document_type,
                'document_number' => $client->document_number,
                'status'          => $client->status,
            ],
            business_id: $business_id,
        );

        return $client;
    }
}
