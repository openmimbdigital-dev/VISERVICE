<?php

namespace App\Support;

class DeleteConfirmationAlert
{
    /**
     * El título lo pone el mensaje que envía cada componente («¿Eliminar esta
     * imagen?»), por eso aquí no se fija uno: sería más genérico y taparía al otro.
     *
     * @return array<string, mixed>
     */
    public static function options(string $on_confirmed = 'confirmed'): array
    {
        return ConfirmationAlert::options(
            on_confirmed: $on_confirmed,
            confirm_text: 'Eliminar',
            confirm_color: '#e11d48',
            icon: 'warning',
            text: 'Esta acción no se puede deshacer.',
        );
    }
}
