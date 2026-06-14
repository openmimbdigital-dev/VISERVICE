<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'client_id', 'plate', 'brand', 'model',
        'year', 'color', 'fuel_type', 'engine_cc', 'vin',
        'km_current', 'status', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status'     => 'boolean',
            'year'       => 'integer',
            'km_current' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function getDisplayNameAttribute(): string
    {
        $parts = array_filter([$this->brand, $this->model, $this->year]);
        $name = implode(' ', $parts);
        return $name ? "{$this->plate} — {$name}" : $this->plate;
    }

    public function getFuelTypeLabelAttribute(): string
    {
        return match ($this->fuel_type) {
            'gasolina' => 'Gasolina',
            'diesel'   => 'Diésel',
            'gas'      => 'Gas',
            'electrico' => 'Eléctrico',
            'hibrido'  => 'Híbrido',
            default    => 'Otro',
        };
    }
}
