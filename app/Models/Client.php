<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id', 'name', 'document_type', 'document_number',
        'phone', 'email', 'address', 'contact_name',
        'status', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function user_historicals(): HasMany
    {
        return $this->hasMany(UserHistorical::class)->orderByDesc('created_at');
    }

    public function equipment_historicals(): HasMany
    {
        return $this->hasMany(EquipmentHistorical::class)->orderByDesc('created_at');
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

    public function getFullDocumentAttribute(): string
    {
        if ($this->document_number) {
            return "{$this->document_type} {$this->document_number}";
        }
        return $this->document_type;
    }
}
