<?php

namespace App\Services\Dian;

use RuntimeException;
use Throwable;

class DianRequestException extends RuntimeException
{
    /** @param array<string, mixed> $response */
    public function __construct(
        string $message,
        public readonly ?int $errorId = null,
        public readonly array $response = [],
        public readonly ?string $endpoint = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /** El proveedor respondió, pero reportando un error de negocio. */
    public static function fromResponse(string $endpoint, array $response): self
    {
        $message = trim((string) (
            $response['error_msg']
            ?? $response['mensaje']
            ?? 'El proveedor de facturación electrónica rechazó la solicitud.'
        ));

        return new self(
            message: $message !== '' ? $message : 'El proveedor de facturación electrónica rechazó la solicitud.',
            errorId: isset($response['error_id']) ? (int) $response['error_id'] : null,
            response: $response,
            endpoint: $endpoint,
        );
    }

    /**
     * ¿El rechazo fue porque ese número de documento ya existe?
     *
     * Es el síntoma de un contador atrasado —típico tras rehacer la base—, y se
     * resuelve tomando el siguiente número, no reintentando el mismo.
     */
    public function isDuplicateDocument(): bool
    {
        $message = mb_strtolower($this->getMessage());

        return str_contains($message, 'duplicad') || str_contains($message, 'ya existe');
    }

    /** La solicitud HTTP falló (red, timeout, 5xx, 429...). */
    public static function transport(string $endpoint, string $message, ?Throwable $previous = null): self
    {
        return new self(
            message: $message,
            errorId: null,
            response: [],
            endpoint: $endpoint,
            previous: $previous,
        );
    }
}
