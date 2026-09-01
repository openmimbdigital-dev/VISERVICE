<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RemissionItem extends Model
{
    protected $fillable = [
        'remission_id',
        'work_order_item_id',
        'product_id',
        'product_type_id',
        'product_category_id',
        'unit_id',
        'description',
        'reference_brand',
        'unit_name',
        'quantity',
        'observations',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity'   => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function remission(): BelongsTo
    {
        return $this->belongsTo(Remission::class);
    }

    public function workOrderItem(): BelongsTo
    {
        return $this->belongsTo(WorkOrderItem::class);
    }

    public function catalogProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
