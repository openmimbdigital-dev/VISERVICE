<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;

    protected $table = 'equipment';

    protected $fillable = [
        'business_id', 'client_id', 'brand_id', 'client_name', 'model_id', 'equipment_type_id',
        'plate', 'name', 'brand_name', 'model_name', 'equipment_type_name', 'year',
        'status', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'year'   => 'integer',
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

    public function attributeValues(): MorphMany
    {
        return $this->morphMany(AttributeEquipmentType::class, 'model');
    }

    public function getSelectLabelAttribute(): string
    {
        return implode(' · ', array_filter([
            $this->name,
            $this->brand_name,
            $this->plate,
        ]));
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

        $business_ids = $user->businessIds();

        if ($business_ids === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn($query->getModel()->getTable() . '.business_id', $business_ids);
    }

    public function getDisplayNameAttribute(): string
    {
        $details = implode(' ', array_filter([
            $this->brand_name,
            $this->model_name,
            $this->year ? (string) $this->year : null,
        ]));

        if ($this->name) {
            return $details ? "{$this->name} ({$details})" : $this->name;
        }

        return $details ? "{$this->plate} — {$details}" : $this->plate;
    }

    public function hasDependencies(): bool
    {
        return $this->workOrders()->exists() || $this->quotations()->exists();
    }

    public function dependencyBlockReason(): ?string
    {
        if ($this->workOrders()->exists()) {
            return 'Tiene órdenes de trabajo asociadas.';
        }

        if ($this->quotations()->exists()) {
            return 'Tiene cotizaciones asociadas.';
        }

        return null;
    }

    public function canDelete(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $user?->can('workshop.equipment.delete') && ! $this->hasDependencies();
    }
}
