<?php

namespace App\Support;

use App\Models\CustomTax;

class AppliedTaxesPreview
{
    /**
     * @param  list<int|string>  $ids
     * @return list<int>
     */
    public static function normalizeIds(array $ids): array
    {
        return array_values(array_unique(array_filter(
            array_map(fn ($id) => (int) $id, $ids),
            fn (int $id) => $id > 0
        )));
    }

    public static function percentageLabel(float $percentage): string
    {
        return rtrim(rtrim(number_format($percentage, 2, '.', ''), '0'), '.');
    }

    /**
     * @param  list<int|string>  $custom_tax_ids
     * @return list<array{id: int, name: string, percentage: float, percentage_label: string, amount: float}>
     */
    public static function lines(array $custom_tax_ids, int $business_id, float $taxable_base): array
    {
        $ids = self::normalizeIds($custom_tax_ids);

        if ($ids === []) {
            return [];
        }

        $query = CustomTax::query()
            ->availableForBusiness($business_id)
            ->whereIn('id', $ids);

        if (auth()->user()) {
            $query->forAuthUser();
        }

        $taxes = $query->get()->keyBy('id');
        $lines = [];

        foreach ($ids as $id) {
            $tax = $taxes->get($id);

            if (! $tax) {
                continue;
            }

            $percentage = (float) $tax->percentage;

            $lines[] = [
                'id'                => (int) $tax->id,
                'name'              => $tax->name,
                'percentage'        => $percentage,
                'percentage_label'  => self::percentageLabel($percentage),
                'amount'            => round($taxable_base * ($percentage / 100), 2),
            ];
        }

        return $lines;
    }

    /**
     * @param  list<array{amount: float}>  $lines
     */
    public static function totalAmount(array $lines): float
    {
        return round(array_sum(array_column($lines, 'amount')), 2);
    }
}
