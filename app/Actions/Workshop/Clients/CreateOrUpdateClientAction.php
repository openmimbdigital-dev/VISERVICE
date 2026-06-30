<?php

namespace App\Actions\Workshop\Clients;

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
            abort_unless((int) $business_id === (int) $user->business_id, 403);
        }

        $attributes = [
            'business_id'     => $business_id,
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

            return $client->fresh();
        }

        $attributes['created_by'] = auth()->id();

        return Client::create($attributes);
    }
}
