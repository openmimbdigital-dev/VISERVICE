<?php

namespace App\Services\Dian;

use App\Models\DianRequestLog;
use App\Models\ElectronicInvoice;
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
    /**
     * La ruta REST real es «estado_documentos», no la «estadoDocumento» del manual
     * (esa responde 404). La confirmó el proveedor por correo.
     */
    private const PATH_DOCUMENT_STATUS = '/PDE/public/api/PDE/estado_documentos';
    private const PATH_DOWNLOAD = '/PDE/public/api/PDE/descargar';
    private const PATH_DETAIL = '/PDE/public/api/PDE/detalle';
    private const PATH_SAVE_COMPANY = '/PDE/public/api/PDE/SaveAutoGestion';
    private const PATH_SAVE_PROFILE = '/PDE/public/api/PDE/SaveAutogestionPerfil';
    private const PATH_QUERY_RESOLUTION = '/PDE/public/api/PDE/ConsultaResolusion';

    private const PATH_LIST = '/PDE/public/api/PDE/listar';

    private const PATH_DELETE = '/PDE/public/api/PDE/borrar';

    /** Tipos de descarga soportados por el endpoint /descargar. */
    public const DOWNLOAD_XML = 1;
    public const DOWNLOAD_PDF = 2;
    public const DOWNLOAD_XML_PDF = 3;

    private ?ElectronicInvoice $context_invoice = null;

    private int $context_attempt = 1;

    public function __construct(private readonly ?string $environment = null)
    {
    }

    public static function for(?string $environment = null): self
    {
        return new self($environment);
    }

    /**
     * Asocia las llamadas siguientes a un documento electrónico, para que queden
     * registradas en la bitácora junto con la factura del sistema.
     */
    public function forInvoice(ElectronicInvoice $electronic_invoice, ?int $attempt = null): self
    {
        $this->context_invoice = $electronic_invoice;
        $this->context_attempt = $attempt ?? max(1, (int) $electronic_invoice->attempts);

        return $this;
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
     * Retira una transacción del proveedor y libera su número.
     *
     * Según el manual, solo funciona mientras el documento no haya salido hacia
     * la DIAN; después, el único camino es una nota crédito. Por eso quien llama
     * tiene que haber comprobado antes en qué estado está.
     *
     * @return array<string, mixed>
     */
    public function deleteTransaction(int $transaction_id): array
    {
        return $this->request(self::PATH_DELETE, ['transaccion' => $transaction_id]);
    }

    /**
     * Transacciones emitidas en una ventana de fechas, de la más reciente a la
     * más antigua.
     *
     * Dos límites del proveedor condicionan cómo se usa: no acepta rangos de más
     * de 30 días, y no deja combinar el filtro de perfil con el de fechas —pide
     * el rango y luego lo rechaza—. Así que la ventana se recorta afuera y
     * distinguir a qué negocio pertenece cada documento se hace por prefijo, que
     * es lo que de todos modos separa a un emisor de otro.
     *
     * @return list<array<string, mixed>>
     */
    public function listTransactions(string $from, string $to, int $page = 1): array
    {
        $response = $this->request(self::PATH_LIST, [
            'busqueda' => [
                ['campo' => 'fd_ini', 'comparacion' => '>=', 'valor' => $from],
                ['campo' => 'fd_end', 'comparacion' => '<=', 'valor' => $to],
            ],
            'page' => (string) $page,
        ]);

        $rows = $response['transaccion_id'] ?? [];

        if (! is_array($rows)) {
            return [];
        }

        return array_values(array_filter($rows, 'is_array'));
    }

    /**
     * Cronología de estados y motivo de rechazo de una transacción.
     *
     * La cronología viene de /estado_documentos, pero el motivo del rechazo solo lo
     * entrega /detalle, así que ese se consulta únicamente cuando hizo falta.
     *
     * @return array{timeline:string, dian_error:?string, raw:array<string,mixed>}
     */
    public function transactionSummary(int $transaction_id): array
    {
        $timeline = $this->documentStatus($transaction_id);

        if ($timeline !== '' && ! str_contains(mb_strtolower($timeline), 'rechaz')) {
            return ['timeline' => $timeline, 'dian_error' => null, 'raw' => []];
        }

        $response = $this->detail($transaction_id);
        $dian_error = trim((string) ($response['error'] ?? ''));

        return [
            'timeline'   => $timeline !== '' ? $timeline : (string) ($response['detalleTransaccion']['estado_id'] ?? ''),
            'dian_error' => $dian_error !== '' ? $dian_error : null,
            'raw'        => $response,
        ];
    }

    /**
     * Cronología de estados ("Recibida, Validada, Enviado a la DIAN...").
     *
     * El endpoint acepta varios documentos a la vez; aquí se consulta uno.
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
     * Cuando el archivo todavía no existe, la plataforma responde sin error global
     * pero con el motivo dentro del documento, así que se devuelve para poder
     * explicárselo al usuario.
     *
     * @return array{data:?string, nombre_archivo:?string, mensaje:?string}
     */
    public function download(int $transaction_id, int $download_type = self::DOWNLOAD_XML_PDF): array
    {
        $response = $this->request(self::PATH_DOWNLOAD, [
            'documentos' => [[
                'transaccion_id' => $transaction_id,
                'tipo_descarga'  => $download_type,
            ]],
        ]);

        $message = null;

        foreach ((array) ($response['documentos'] ?? []) as $document) {
            if (filled($document['data'] ?? null)) {
                return [
                    'data'           => (string) $document['data'],
                    'nombre_archivo' => (string) ($document['nombre_archivo'] ?? "transaccion-{$transaction_id}"),
                    'mensaje'        => null,
                ];
            }

            $message ??= trim((string) ($document['error_msg'] ?? '')) ?: null;
        }

        return ['data' => null, 'nombre_archivo' => null, 'mensaje' => $message];
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
        $started_at = microtime(true);
        $status = null;

        try {
            if ($authenticated) {
                $payload = ['token' => $this->token()] + $payload;
            }

            $response = $this->send($path, $payload);

            // Un token vencido se detecta reintentando una vez con credenciales frescas.
            if ($authenticated && $this->isExpiredTokenResponse($response)) {
                $payload['token'] = $this->token(force_refresh: true);
                $response = $this->send($path, $payload);
            }

            $status = $response->status();
            $body = $this->decode($path, $response);

            if (isset($body['error_id']) && (int) $body['error_id'] !== 0) {
                throw DianRequestException::fromResponse($path, $body);
            }
        } catch (DianRequestException $exception) {
            $this->log($path, $payload, $exception->response, false, $status, $started_at, $exception);

            throw $exception;
        }

        $this->log($path, $payload, $body, true, $status, $started_at);

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

    /**
     * Registra la llamada en la bitácora. Nunca debe interrumpir la emisión:
     * si el registro falla, la operación continúa.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $body
     */
    private function log(
        string $path,
        array $payload,
        array $body,
        bool $success,
        ?int $status,
        float $started_at,
        ?DianRequestException $exception = null,
    ): void {
        try {
            DianRequestLog::query()->create([
                'business_id'           => $this->context_invoice?->business_id,
                'electronic_invoice_id' => $this->context_invoice?->id,
                'work_order_invoice_id' => $this->context_invoice?->work_order_invoice_id,
                'operation'             => $this->operationFor($path),
                'endpoint'              => $path,
                'environment'           => $this->environment(),
                'success'               => $success,
                'http_status'           => $status,
                'error_id'              => $exception?->errorId ?? (isset($body['error_id']) ? (int) $body['error_id'] : null),
                'error_message'         => $exception ? $this->providerMessage($body, $exception) : null,
                'request_payload'       => $this->readablePayload($payload),
                'response_payload'      => $body !== [] ? $this->encode($body) : null,
                'attempt'               => $this->context_attempt,
                'duration_ms'           => (int) round((microtime(true) - $started_at) * 1000),
                'created_by'            => auth()->id(),
            ]);
        } catch (Throwable) {
            // La bitácora es informativa: no puede tumbar la emisión.
        }
    }

    /** @param array<string, mixed> $body */
    private function providerMessage(array $body, DianRequestException $exception): string
    {
        // El detalle útil del rechazo suele venir en "mensaje", no en "error_msg".
        $message = trim((string) ($body['mensaje'] ?? ''));

        return $message !== '' ? $message : $exception->getMessage();
    }

    /**
     * Payload legible para la bitácora: sin credenciales y con el documento decodificado.
     *
     * @param  array<string, mixed>  $payload
     */
    private function readablePayload(array $payload): string
    {
        unset($payload['token'], $payload['password']);

        if (isset($payload['data']) && is_string($payload['data'])) {
            $decoded = base64_decode($payload['data'], strict: true);
            $payload['data'] = $decoded !== false ? $decoded : '[base64]';
        }

        return $this->encode($payload);
    }

    /** @param array<string, mixed> $value */
    private function encode(array $value): string
    {
        return (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    private function operationFor(string $path): string
    {
        return match ($path) {
            self::PATH_AUTHENTICATION    => DianRequestLog::OPERATION_AUTHENTICATE,
            self::PATH_EMIT              => DianRequestLog::OPERATION_EMIT,
            self::PATH_DOCUMENT_STATUS   => DianRequestLog::OPERATION_STATUS,
            self::PATH_DOWNLOAD          => DianRequestLog::OPERATION_DOWNLOAD,
            self::PATH_DETAIL            => DianRequestLog::OPERATION_DETAIL,
            self::PATH_SAVE_COMPANY,
            self::PATH_SAVE_PROFILE      => DianRequestLog::OPERATION_REGISTER_COMPANY,
            self::PATH_QUERY_RESOLUTION  => DianRequestLog::OPERATION_QUERY_RESOLUTION,
            default                      => trim($path, '/'),
        };
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
