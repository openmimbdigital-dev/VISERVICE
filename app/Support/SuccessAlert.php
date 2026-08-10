<?php

namespace App\Support;

class SuccessAlert
{
    public static function options(string $title, ?string $text = null): array
    {
        $options = [
            'title' => $title,
            'icon' => 'success',
            'position' => 'center',
            'toast' => false,
            'showConfirmButton' => true,
            'confirmButtonText' => 'Aceptar',
            'confirmButtonColor' => '#4f46e5',
            'customClass' => [
                'popup' => 'swal-viservice-popup',
                'title' => 'swal-viservice-title',
                'htmlContainer' => 'swal-viservice-html',
                'confirmButton' => 'swal-success-button',
            ],
        ];

        if ($text !== null && $text !== '') {
            $options['text'] = $text;
        }

        return $options;
    }
}
