<?php

namespace App\Support;

class ActionConfirmationAlert
{
    /**
     * Opciones de confirmación para acciones que no son eliminaciones.
     */
    public static function options(
        string $title,
        string $text,
        string $confirm_button_text,
        string $on_confirmed,
        string $confirm_button_color = '#4f46e5',
    ): array {
        return [
            'title'              => $title,
            'text'               => $text,
            'showConfirmButton'  => true,
            'confirmButtonText'  => $confirm_button_text,
            'confirmButtonColor' => $confirm_button_color,
            'onConfirmed'        => $on_confirmed,
            'showCancelButton'   => true,
            'cancelButtonText'   => 'Cancelar',
            'cancelButtonColor'  => '#64748b',
            'toast'              => false,
            'icon'               => 'warning',
            'position'           => 'center',
            'customClass'        => [
                'popup'         => 'swal-viservice-popup',
                'title'         => 'swal-viservice-title',
                'htmlContainer' => 'swal-viservice-html',
                'confirmButton' => 'swal-confirm-button',
                'cancelButton'  => 'swal-cancel-button',
            ],
        ];
    }
}
