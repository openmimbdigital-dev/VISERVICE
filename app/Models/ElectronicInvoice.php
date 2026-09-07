<?php

namespace App\Models;

use App\Enums\ElectronicInvoiceStatus;
use App\Models\Concerns\BelongsToBusinessTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectronicInvoice extends Model
{
    use BelongsToBusinessTenant;

    protected $fillable = [
        'business_id',
        'work_order_invoice_id',
        'document_type',
        'environment',
        'prefix',
        'consecutive',
        'document_number',
        'status',
        'transaction_id',
        'cufe',
        'qr_code',
        'dian_status',
        'error_id',
        'error_message',
        'request_document',
        'response_payload',
        'attempts',
        'issued_at',
        'sent_at',
        'accepted_at',
        'status_checked_at',
        'xml_path',
        'pdf_path',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status'            => ElectronicInvoiceStatus::class,
            'consecutive'       => 'integer',
            'transaction_id'    => 'integer',
            'error_id'          => 'integer',
            'attempts'          => 'integer',
            'response_payload'  => 'array',
            'issued_at'         => 'datetime',
            'sent_at'           => 'datetime',
            'accepted_at'       => 'datetime',
            'status_checked_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(WorkOrderInvoice::class, 'work_order_invoice_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function requestLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DianRequestLog::class)->latest();
    }

    public function hasFiles(): bool
    {
        return $this->xml_path !== null || $this->pdf_path !== null;
    }

    /** Guarda la cronología reportada por el proveedor y deriva el estado local. */
    public function applyProviderTimeline(string $timeline): self
    {
        $status = ElectronicInvoiceStatus::fromProviderTimeline($timeline, $this->status);

        $this->forceFill([
            'dian_status'       => $timeline !== '' ? $timeline : $this->dian_status,
            'status'            => $status,
            'status_checked_at' => now(),
            'accepted_at'       => $status === ElectronicInvoiceStatus::Accepted
                ? ($this->accepted_at ?? now())
                : $this->accepted_at,
        ])->save();

        return $this;
    }

    /**
     * Cronología de estados reportada por el proveedor, ya separada.
     *
     * @return list<string>
     */
    public function statusTimeline(): array
    {
        if (blank($this->dian_status)) {
            return [];
        }

        return array_values(array_filter(array_map(
            'trim',
            explode(',', $this->dian_status)
        )));
    }
}
