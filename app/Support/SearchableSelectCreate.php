<?php

namespace App\Support;

use App\Models\Client;

class SearchableSelectCreate
{
    /**
     * Configuración de alta rápida según el modelo del select.
     *
     * @return array{component: string, permission: string, button: string, title: string}|null
     */
    public static function for(string $modelClass): ?array
    {
        return match ($modelClass) {
            Client::class => [
                'component'  => 'ui.searchable-create.client-modal',
                'permission' => 'workshop.clients.create',
                'button'     => 'Crear cliente',
                'title'      => 'Nuevo cliente',
            ],
            default => null,
        };
    }
}
