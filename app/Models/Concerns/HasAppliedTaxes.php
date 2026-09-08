<?php

namespace App\Models\Concerns;

use App\Models\AppliedTax;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasAppliedTaxes
{
    public function appliedTaxes(): MorphMany
    {
        return $this->morphMany(AppliedTax::class, 'taxable')->orderBy('id');
    }

    /** @return list<int> */
    public function appliedCustomTaxIds(): array
    {
        $taxes = $this->relationLoaded('appliedTaxes')
            ? $this->appliedTaxes
            : $this->appliedTaxes()->get();

        return $taxes
            ->pluck('custom_tax_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function refreshAppliedTaxAmounts(float $taxable_base): float
    {
        $total = 0.0;

        foreach ($this->appliedTaxes()->get() as $row) {
            $amount = round($taxable_base * ((float) $row->tax_percentage / 100), 2);
            $row->update(['tax_amount' => $amount]);
            $total += $amount;
        }

        return round($total, 2);
    }

    public function appliedTaxableBase(): float
    {
        return max(0, round((float) $this->subtotal, 2));
    }

    public function effectiveTaxPercentage(?float $taxable_base = null): float
    {
        $base = $taxable_base ?? $this->appliedTaxableBase();

        if ($base <= 0) {
            return 0.0;
        }

        return round(((float) $this->tax_amount / $base) * 100, 2);
    }

    protected static function bootHasAppliedTaxes(): void
    {
        static::forceDeleting(function ($model) {
            $model->appliedTaxes()->delete();
        });
    }
}
