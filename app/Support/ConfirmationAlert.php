<?php

namespace App\Support;

/**
 * Opciones base de las confirmaciones del proyecto, para que todos los diálogos
 * se vean igual (mismo tema que el resto de alertas de SweetAlert).
 */
class ConfirmationAlert
{
    /**
     * @param  string  $on_confirmed  Evento Livewire que se despacha al aceptar.
     * @return array<string, mixed>
     */
    public static function options(
        string $on_confirmed = 'confirmed',
        string $confirm_text = 'Confirmar',
        string $confirm_color = '#4f46e5',
        string $icon = 'question',
        ?string $text = null,
    ): array {
        // Solo se descartan los nulos: «toast => false» debe llegar tal cual.
        return array_filter([
            'text'               => $text,
            'icon'               => $icon,
            'position'           => 'center',
            'toast'              => false,
            'showConfirmButton'  => true,
            'confirmButtonText'  => $confirm_text,
            'confirmButtonColor' => $confirm_color,
            'showCancelButton'   => true,
            'cancelButtonText'   => 'Cancelar',
            'cancelButtonColor'  => '#64748b',
            'onConfirmed'        => $on_confirmed,
            'customClass'        => [
                'popup'         => 'swal-viservice-popup',
                'title'         => 'swal-viservice-title',
                'htmlContainer' => 'swal-viservice-html',
                'confirmButton' => 'swal-confirm-button',
                'cancelButton'  => 'swal-cancel-button',
            ],
        ], static fn ($value) => $value !== null);
    }
}
