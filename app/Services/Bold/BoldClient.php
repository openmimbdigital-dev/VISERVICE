<?php

namespace App\Services\Bold;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Cliente REST de la pasarela de pagos Bold.
 *
 * Cubre lo necesario para cobrar suscripciones en línea: consultar los métodos
 * habilitados, crear un link de pago y consultar en qué va.
 *
 * La autenticación es una cabecera «Authorization: x-api-key <llave>», que no es
 * el esquema Bearer habitual: la llave viaja con ese prefijo literal.
 */
class BoldClient
{
    private const PATH_PAYMENT_METHODS = '/online/link/v1/payment_methods';

    private const PATH_LINKS = '/online/link/v1';

    public static function make(): self
    {
        return new self();
    }

    public function isConfigured(): bool
    {
        return filled(config('bold.identity_key')) && filled(config('bold.base_url'));
    }

    /**
     * Métodos de pago habilitados con sus montos mínimo y máximo.
     *
     * @return array<string, array{min: int, max: int}>
     */
    public function paymentMethods(): array
    {
        $response = $this->request('get', self::PATH_PAYMENT_METHODS);

        return (array) ($response['payload']['payment_methods'] ?? []);
    }

    /**
     * Crea un link de pago por un monto cerrado.
     *
     * @param  array<string, mixed>  $taxes  Impuestos, con la forma que pide Bold.
     * @return array{payment_link: string, url: string, raw: array<string, mixed>}
     */
    public function createLink(
        float $total_amount,
        string $reference,
        string $description,
        ?string $callback_url = null,
        ?string $payer_email = null,
        array $taxes = [],
    ): array {
        $payload = array_filter([
            'amount_type'  => 'CLOSE',
            'amount'       => array_filter([
                'currency'     => (string) config('bold.link.currency', 'COP'),
                'total_amount' => round($total_amount, 2),
                'taxes'        => $taxes !== [] ? array_values($taxes) : null,
            ], fn ($value) => $value !== null),
            'reference'    => $reference,
            'description'  => $this->trimDescription($description),
            'callback_url' => $callback_url,
            'payer_email'  => $payer_email,
            'expiration_date'  => $this->expirationDate(),
            'payment_methods'  => (array) config('bold.link.payment_methods') ?: null,
        ], fn ($value) => $value !== null && $value !== []);

        $response = $this->request('post', self::PATH_LINKS, $payload);

        $link = (string) ($response['payload']['payment_link'] ?? '');
        $url = (string) ($response['payload']['url'] ?? '');

        if ($link === '' || $url === '') {
            throw BoldRequestException::fromResponse(self::PATH_LINKS, $response);
        }

        return ['payment_link' => $link, 'url' => $url, 'raw' => $response];
    }

    /**
     * Estado del link: ACTIVE, PROCESSING, PAID o EXPIRED.
     *
     * @return array<string, mixed>
     */
    public function linkStatus(string $payment_link): array
    {
        return $this->request('get', self::PATH_LINKS.'/'.$payment_link);
    }

    /**
     * Bold pide la vigencia en nanosegundos desde la época Unix.
     */
    private function expirationDate(): ?int
    {
        $hours = (int) config('bold.link.expiration_hours', 0);

        if ($hours <= 0) {
            return null;
        }

        return now()->addHours($hours)->getTimestamp() * 1_000_000_000;
    }

    /** Bold exige entre 2 y 100 caracteres. */
    private function trimDescription(string $description): string
    {
        $description = trim($description);

        if (mb_strlen($description) > 100) {
            return mb_substr($description, 0, 99).'…';
        }

        return mb_strlen($description) < 2 ? 'Suscripción' : $description;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, array $payload = []): array
    {
        if (! $this->isConfigured()) {
            throw BoldRequestException::transport($path, 'La pasarela Bold no está configurada.');
        }

        try {
            $request = Http::baseUrl((string) config('bold.base_url'))
                ->timeout((int) config('bold.timeout', 20))
                ->acceptJson()
                ->asJson()
                // No es «Bearer»: Bold espera el prefijo literal x-api-key.
                ->withHeaders(['Authorization' => 'x-api-key '.config('bold.identity_key')]);

            $response = $method === 'get'
                ? $request->get($path)
                : $request->post($path, $payload);
        } catch (ConnectionException $exception) {
            throw BoldRequestException::transport($path, 'No fue posible conectar con Bold: '.$exception->getMessage(), $exception);
        } catch (Throwable $exception) {
            throw BoldRequestException::transport($path, 'Error inesperado al contactar a Bold: '.$exception->getMessage(), $exception);
        }

        return $this->decode($path, $response);
    }

    /** @return array<string, mixed> */
    private function decode(string $path, Response $response): array
    {
        $body = $response->json();

        if (! is_array($body)) {
            throw BoldRequestException::transport(
                $path,
                'Bold devolvió una respuesta no válida (HTTP '.$response->status().').'
            );
        }

        if ($response->failed() || ! empty($body['errors'])) {
            throw BoldRequestException::fromResponse($path, $body);
        }

        return $body;
    }
}
