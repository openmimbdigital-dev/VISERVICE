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

    /**
     * El proveedor respondió, pero reportando un error de negocio.
     *
     * El motivo no siempre viaja en el mismo campo: los rechazos de emisión lo
     * ponen en «mensaje» y dejan «error_msg» vacío. Hay que recorrerlos hasta dar
     * con uno que traiga algo —no basta con el operador ??, que solo salta los
     * nulos—, porque de ese texto depende reconocer un documento duplicado y
     * reintentar con el siguiente número.
     */
    public static function fromResponse(string $endpoint, array $response): self
    {
        $message = '';

        foreach (['error_msg', 'mensaje'] as $field) {
            $candidate = trim((string) ($response[$field] ?? ''));

            if ($candidate !== '') {
                $message = $candidate;

                break;
            }
        }

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
        // Se mira el mensaje y también la respuesta cruda: dar por bueno un
        // consecutivo quemado cuesta un documento rechazado y una intervención a
        // mano, así que no conviene depender de que el motivo haya caído en el
        // campo que esperábamos.
        $haystack = mb_strtolower(implode(' ', array_filter([
            $this->getMessage(),
            (string) ($this->response['mensaje'] ?? ''),
            (string) ($this->response['error_msg'] ?? ''),
        ])));

        return str_contains($haystack, 'duplicad') || str_contains($haystack, 'ya existe');
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
