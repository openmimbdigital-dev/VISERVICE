<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;

    protected $table = 'equipment';

    protected $fillable = [
        'business_id', 'client_id', 'brand_id', 'model_id', 'equipment_type_id',
        'plate', 'brand', 'model', 'year', 'km_current',
        'status', 'notes', 'created_by',
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

    public function equipmentBrand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id')->withTrashed();
    }

    public function equipmentModel(): BelongsTo
    {
        return $this->belongsTo(EquipmentModel::class, 'model_id')->withTrashed();
    }

    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class, 'equipment_type_id')->withTrashed();
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

    public function scopeForAuthUser($query)
    {
        $user = auth()->user();

        if ($user?->hasRole('superAdmin')) {
            return $query;
        }

        return $query->where($query->getModel()->getTable() . '.business_id', $user->business_id);
    }

    public function getDisplayNameAttribute(): string
    {
        $parts = array_filter([$this->brand, $this->model, $this->year]);
        $name = implode(' ', $parts);
        return $name ? "{$this->plate} — {$name}" : $this->plate;
    }
}
