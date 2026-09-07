<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusinessTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bitácora de cada llamada al proveedor de facturación electrónica.
 *
 * Permite ver qué se envió, qué respondió el proveedor y por qué falló un intento,
 * sin depender de que el registro del documento haya conservado el último error.
 */
class DianRequestLog extends Model
{
    use BelongsToBusinessTenant;

    /** Operaciones registradas. */
    public const OPERATION_AUTHENTICATE = 'authenticate';
    public const OPERATION_EMIT = 'emit';
    public const OPERATION_STATUS = 'status';
    public const OPERATION_DOWNLOAD = 'download';
    public const OPERATION_DETAIL = 'detail';
    public const OPERATION_REGISTER_COMPANY = 'register_company';
    public const OPERATION_QUERY_RESOLUTION = 'query_resolution';

    protected $fillable = [
        'business_id',
        'electronic_invoice_id',
        'work_order_invoice_id',
        'operation',
        'endpoint',
        'environment',
        'success',
        'http_status',
        'error_id',
        'error_message',
        'request_payload',
        'response_payload',
        'attempt',
        'duration_ms',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'success'     => 'boolean',
            'http_status' => 'integer',
            'error_id'    => 'integer',
            'attempt'     => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function electronicInvoice(): BelongsTo
    {
        return $this->belongsTo(ElectronicInvoice::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(WorkOrderInvoice::class, 'work_order_invoice_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function operationLabel(): string
    {
        return match ($this->operation) {
            self::OPERATION_AUTHENTICATE     => 'Autenticación',
            self::OPERATION_EMIT             => 'Emisión',
            self::OPERATION_STATUS           => 'Consulta de estado',
            self::OPERATION_DOWNLOAD         => 'Descarga de archivos',
            self::OPERATION_DETAIL           => 'Detalle de transacción',
            self::OPERATION_REGISTER_COMPANY => 'Registro de empresa',
            self::OPERATION_QUERY_RESOLUTION => 'Consulta de resolución',
            default                          => $this->operation,
        };
    }

    public function badgeClass(): string
    {
        return $this->success
            ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
            : 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20';
    }
}
