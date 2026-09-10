<?php

namespace App\Services\Bold;

use RuntimeException;
use Throwable;

/**
 * Falla al hablar con Bold. Guarda la respuesta cruda para poder mostrarla en la
 * bitácora sin tener que adivinar qué contestó la pasarela.
 */
class BoldRequestException extends RuntimeException
{
    /** @param array<string, mixed> $response */
    public function __construct(
        string $message,
        public readonly string $endpoint = '',
        public readonly array $response = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /** @param array<string, mixed> $response */
    public static function fromResponse(string $endpoint, array $response): self
    {
        $errors = (array) ($response['errors'] ?? []);

        $message = collect($errors)
            ->map(fn ($error) => is_array($error)
                ? ($error['message'] ?? $error['description'] ?? json_encode($error, JSON_UNESCAPED_UNICODE))
                : (string) $error)
            ->filter()
            ->join(' · ');

        return new self(
            $message !== '' ? $message : 'Bold rechazó la solicitud.',
            $endpoint,
            $response,
        );
    }

    public static function transport(string $endpoint, string $message, ?Throwable $previous = null): self
    {
        return new self($message, $endpoint, [], $previous);
    }
}
