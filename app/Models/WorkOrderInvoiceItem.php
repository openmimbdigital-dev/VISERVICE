<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderInvoiceItem extends Model
{
    protected $fillable = [
        'work_order_invoice_id',
        'work_order_item_id',
        'quantity',
        'quantity_complete',
        'quantity_canceled',
    ];

    protected function casts(): array
    {
        return [
            'quantity'          => 'decimal:2',
            'quantity_complete' => 'decimal:2',
            'quantity_canceled' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(WorkOrderInvoice::class, 'work_order_invoice_id');
    }

    public function workOrderItem(): BelongsTo
    {
        return $this->belongsTo(WorkOrderItem::class);
    }
}
