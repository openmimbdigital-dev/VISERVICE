<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderAssociatedDocument extends Model
{
    protected $fillable = [
        'work_order_id',
        'associated_document_type_id',
        'name',
        'value',
        'document_send',
    ];

    protected function casts(): array
    {
        return [
            'document_send' => 'boolean',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function associatedDocumentType(): BelongsTo
    {
        return $this->belongsTo(AssociatedDocumentType::class);
    }
}
