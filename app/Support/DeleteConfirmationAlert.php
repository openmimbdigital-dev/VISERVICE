<?php

namespace App\Support;

class DeleteConfirmationAlert
{
    public static function options(string $on_confirmed = 'confirmed'): array
    {
        return [
            'title'               => 'Confirmar eliminación',
            'text'                => 'Esta acción no se puede deshacer.',
            'showConfirmButton'   => true,
            'confirmButtonText'   => 'Eliminar',
            'confirmButtonColor'  => '#e11d48',
            'onConfirmed'         => $on_confirmed,
            'showCancelButton'    => true,
            'cancelButtonText'    => 'Cancelar',
            'cancelButtonColor'   => '#64748b',
            'toast'               => false,
            'icon'                => 'warning',
            'position'            => 'center',
            'customClass'         => [
                'popup'         => 'swal-viservice-popup',
                'title'         => 'swal-viservice-title',
                'htmlContainer' => 'swal-viservice-html',
                'confirmButton' => 'swal-confirm-button',
                'cancelButton'  => 'swal-cancel-button',
            ],
        ];
    }
}
