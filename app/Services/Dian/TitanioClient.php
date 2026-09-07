<?php

namespace App\Services\Dian;

use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Cliente REST de la plataforma TITANIO (Delcop) para facturación electrónica DIAN.
 *
 * Todas las llamadas se autentican con un token de la empresa gestionadora, que se
 * cachea hasta su vencimiento porque la plataforma limita a 120 peticiones por minuto.
 */
class TitanioClient
{
    private const PATH_AUTHENTICATION = '/PDE/public/api/PDE/authentication';
    private const PATH_EMIT = '/PDE/public/api/PDE/emitir_v2';
    private const PATH_DOCUMENT_STATUS = '/PDE/public/api/PDE/estadoDocumento';
    private const PATH_DOWNLOAD = '/PDE/public/api/PDE/descargar';
    private const PATH_DETAIL = '/PDE/public/api/PDE/detalle';
    private const PATH_SAVE_COMPANY = '/PDE/public/api/PDE/SaveAutoGestion';
    private const PATH_SAVE_PROFILE = '/PDE/public/api/PDE/SaveAutogestionPerfil';
    private const PATH_QUERY_RESOLUTION = '/PDE/public/api/PDE/ConsultaResolusion';

    /** Tipos de descarga soportados por el endpoint /descargar. */
    public const DOWNLOAD_XML = 1;
    public const DOWNLOAD_PDF = 2;
    public const DOWNLOAD_XML_PDF = 3;

    public function __construct(private readonly ?string $environment = null)
    {
    }

    public static function for(?string $environment = null): self
    {
        return new self($environment);
    }

    public function environment(): string
    {
        return $this->environment ?: (string) config('dian.environment', 'test');
    }

    public function baseUrl(): string
    {
        $urls = (array) config('dian.titanio.base_url');

        return (string) ($urls[$this->environment()] ?? $urls['test'] ?? '');
    }

    /**
     * Token de autorización vigente. Se reutiliza desde caché mientras no venza.
     */
    public function token(bool $force_refresh = false): string
    {
        $cache_key = $this->tokenCacheKey();

        if ($force_refresh) {
            Cache::forget($cache_key);
        }

        $cached = Cache::get($cache_key);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $credentials = (array) config('dian.titanio');

        $payload = [
            'NIT'      => (int) preg_replace('/\D/', '', (string) ($credentials['nit'] ?? '')),
            'usuario'  => (string) ($credentials['user'] ?? ''),
            'password' => (string) ($credentials['password'] ?? ''),
        ];

        $response = $this->request(self::PATH_AUTHENTICATION, $payload, authenticated: false);

        $token = (string) ($response['token'] ?? '');

        if ($token === '') {
            throw DianRequestException::fromResponse(self::PATH_AUTHENTICATION, $response);
        }

        Cache::put($cache_key, $token, $this->tokenLifetime($response['ven'] ?? null));

        return $token;
    }

    /**
     * Emite un documento (validación previa). El XML se envía codificado en base 64.
     *
     * @return array{tr_id:int, cufe:string, qr:string, mensaje:string, raw:array<string,mixed>}
     */
    public function emit(int $tr_tipo_id, string $xml): array
    {
        $response = $this->request(self::PATH_EMIT, [
            'tr_tipo_id' => $tr_tipo_id,
            'data'       => base64_encode($xml),
        ]);

        $transaction_id = (int) ($response['tr_id'] ?? 0);

        if ($transaction_id <= 0) {
            throw DianRequestException::fromResponse(self::PATH_EMIT, $response);
        }

        return [
            'tr_id'   => $transaction_id,
            'cufe'    => (string) ($response['cufe'] ?? ''),
            'qr'      => (string) ($response['qr'] ?? ''),
            'mensaje' => (string) ($response['mensaje'] ?? $response['error_msg'] ?? ''),
            'raw'     => $response,
        ];
    }

    /**
     * Cronología de estados de una transacción ("Recibida, Validada, Enviado a DIAN...").
     */
    public function documentStatus(int $transaction_id): string
    {
        $response = $this->request(self::PATH_DOCUMENT_STATUS, [
            'documentos' => [['transaccion_id' => $transaction_id]],
        ]);

        foreach ((array) ($response['documentos'] ?? []) as $document) {
            if ((int) ($document['transaccion_id'] ?? 0) === $transaction_id) {
                return (string) ($document['estado'] ?? '');
            }
        }

        return '';
    }

    /**
     * Descarga los archivos de una transacción.
     *
     * @return array{data:string, nombre_archivo:string}|null Contenido en base 64.
     */
    public function download(int $transaction_id, int $download_type = self::DOWNLOAD_XML_PDF): ?array
    {
        $response = $this->request(self::PATH_DOWNLOAD, [
            'documentos' => [[
                'transaccion_id' => $transaction_id,
                'tipo_descarga'  => $download_type,
            ]],
        ]);

        foreach ((array) ($response['documentos'] ?? []) as $document) {
            if (blank($document['data'] ?? null)) {
                continue;
            }

            return [
                'data'           => (string) $document['data'],
                'nombre_archivo' => (string) ($document['nombre_archivo'] ?? "transaccion-{$transaction_id}"),
            ];
        }

        return null;
    }

    /** Detalle completo de la transacción, incluida la respuesta de la DIAN. */
    public function detail(int $transaction_id): array
    {
        return $this->request(self::PATH_DETAIL, ['transaccion_id' => $transaction_id]);
    }

    /**
     * Registra una empresa emisora bajo la cuenta gestionadora (autogestión).
     *
     * @param  array<string, mixed>  $company  registros_empresa
     * @param  array<string, mixed>  $profile  registros_perfil
     * @return array{tr_tipo_id:?int, cfg_lote_id:?int, profile_name:?string, raw:array<string,mixed>}
     */
    public function createCompany(string $company_nit, array $company, array $profile): array
    {
        $response = $this->request(self::PATH_SAVE_COMPANY, [
            'empresaId'         => $company_nit,
            'registros_empresa' => $company,
            'registros_perfil'  => $profile,
        ]);

        return $this->parseProfileResponse(self::PATH_SAVE_COMPANY, $response);
    }

    /**
     * Crea un perfil/configuración adicional para una empresa ya registrada.
     *
     * @param  array<string, mixed>  $profile  registros_resolucion
     */
    public function createProfile(string $company_nit, array $profile): array
    {
        $response = $this->request(self::PATH_SAVE_PROFILE, [
            'empresaId'             => $company_nit,
            'registros_resolucion'  => $profile,
            'empresagestionadora'   => (string) config('dian.titanio.nit'),
        ]);

        return $this->parseProfileResponse(self::PATH_SAVE_PROFILE, $response);
    }

    /** Consulta los rangos y claves técnicas de la resolución en producción. */
    public function queryResolution(string $nit): array
    {
        return $this->request(self::PATH_QUERY_RESOLUTION, [
            'nit' => (int) preg_replace('/\D/', '', $nit),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function request(string $path, array $payload, bool $authenticated = true): array
    {
        if ($authenticated) {
            $payload = ['token' => $this->token()] + $payload;
        }

        $response = $this->send($path, $payload);

        // Un token vencido se detecta reintentando una vez con credenciales frescas.
        if ($authenticated && $this->isExpiredTokenResponse($response)) {
            $payload['token'] = $this->token(force_refresh: true);
            $response = $this->send($path, $payload);
        }

        $body = $this->decode($path, $response);

        if (isset($body['error_id']) && (int) $body['error_id'] !== 0) {
            throw DianRequestException::fromResponse($path, $body);
        }

        return $body;
    }

    /** @param array<string, mixed> $payload */
    private function send(string $path, array $payload): Response
    {
        $base_url = $this->baseUrl();

        if ($base_url === '') {
            throw DianRequestException::transport(
                $path,
                'No hay URL configurada para el entorno «'.$this->environment().'» de facturación electrónica.'
            );
        }

        try {
            return Http::baseUrl($base_url)
                ->timeout((int) config('dian.titanio.timeout', 30))
                ->acceptJson()
                ->asJson()
                ->post($path, $payload);
        } catch (ConnectionException $exception) {
            throw DianRequestException::transport(
                $path,
                'No fue posible conectar con el proveedor de facturación electrónica: '.$exception->getMessage(),
                $exception,
            );
        } catch (Throwable $exception) {
            throw DianRequestException::transport(
                $path,
                'Error inesperado al contactar al proveedor de facturación electrónica: '.$exception->getMessage(),
                $exception,
            );
        }
    }

    /** @return array<string, mixed> */
    private function decode(string $path, Response $response): array
    {
        if ($response->status() === 429) {
            $retry_after = $response->header('Retry-After');

            throw DianRequestException::transport(
                $path,
                'El proveedor limitó las solicitudes (máximo 120 por minuto).'
                    .($retry_after ? " Reintenta en {$retry_after} segundos." : '')
            );
        }

        $body = $response->json();

        if (! is_array($body)) {
            throw DianRequestException::transport(
                $path,
                'El proveedor devolvió una respuesta no válida (HTTP '.$response->status().').'
            );
        }

        if ($response->failed() && ! isset($body['error_id'])) {
            throw DianRequestException::fromResponse($path, $body);
        }

        return $body;
    }

    /** @param array<string, mixed> $response */
    private function parseProfileResponse(string $path, array $response): array
    {
        $data = (array) ($response['data'] ?? []);

        if (isset($response['statusCode']) && (int) $response['statusCode'] !== 200) {
            throw DianRequestException::fromResponse($path, $response);
        }

        return [
            'tr_tipo_id'   => isset($data['trTipoIds'][0]['trTipo']) ? (int) $data['trTipoIds'][0]['trTipo'] : null,
            'cfg_lote_id'  => isset($data['cfgLoteIds'][0]['cfgLoteId']) ? (int) $data['cfgLoteIds'][0]['cfgLoteId'] : null,
            'profile_name' => isset($data['nombre_perfil']) ? (string) $data['nombre_perfil'] : null,
            'raw'          => $response,
        ];
    }

    private function isExpiredTokenResponse(Response $response): bool
    {
        if ($response->status() === 401) {
            return true;
        }

        $body = $response->json();

        if (! is_array($body)) {
            return false;
        }

        $message = mb_strtolower((string) ($body['error_msg'] ?? $body['mensaje'] ?? ''));

        return str_contains($message, 'token');
    }

    private function tokenCacheKey(): string
    {
        $nit = preg_replace('/\D/', '', (string) config('dian.titanio.nit'));

        return "dian:titanio:{$this->environment()}:{$nit}:token";
    }

    private function tokenLifetime(mixed $expires_at): int
    {
        $margin = (int) config('dian.titanio.token_safety_margin_seconds', 120);

        if (! is_string($expires_at) || trim($expires_at) === '') {
            return 300;
        }

        try {
            $seconds = (int) now()->diffInSeconds(Carbon::parse($expires_at), absolute: false);
        } catch (Throwable) {
            return 300;
        }

        return max(60, $seconds - $margin);
    }
}
