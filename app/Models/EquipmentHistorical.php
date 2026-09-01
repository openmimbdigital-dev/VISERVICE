<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EquipmentHistorical extends Model
{
    public $timestamps = false;

    protected $table = 'equipment_historical';

    protected $fillable = [
        'business_id',
        'equipment_id',
        'client_id',
        'client_name',
        'equipment_plate',
        'equipment_label',
        'user_id',
        'action',
        'module',
        'description',
        'subject_type',
        'subject_id',
        'subject_reference',
        'subject_status',
        'items',
        'subtotal',
        'tax_percentage',
        'tax_amount',
        'total',
        'properties',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'items'          => 'array',
            'properties'     => 'array',
            'subtotal'       => 'decimal:2',
            'tax_percentage' => 'decimal:2',
            'tax_amount'     => 'decimal:2',
            'total'          => 'decimal:2',
            'created_at'     => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function booted(): void
    {
        static::creating(function (EquipmentHistorical $log) {
            if ($log->created_at === null) {
                $log->created_at = now();
            }
        });
    }
}
