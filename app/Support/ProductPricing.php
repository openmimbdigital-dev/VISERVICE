<?php

namespace App\Support;

final class ProductPricing
{
    /**
     * @return array{sale_price: float, profit_margin: float}
     */
    public static function fromCostAndPercentage(float $cost, float $percent): array
    {
        $sale_price    = round($cost * (1 + ($percent / 100)), 2);
        $profit_margin = round($sale_price - $cost, 2);

        return [
            'sale_price'    => $sale_price,
            'profit_margin' => $profit_margin,
        ];
    }

    /**
     * @return array{profit_percentage: float|null, profit_margin: float}
     */
    public static function fromCostAndSale(float $cost, float $sale): array
    {
        $profit_margin = round($sale - $cost, 2);

        return [
            'profit_percentage' => $cost > 0 ? round(($profit_margin / $cost) * 100, 2) : null,
            'profit_margin'     => $profit_margin,
        ];
    }

    /**
     * @return array{profit_percentage: float|null, sale_price: float}
     */
    public static function fromCostAndMargin(float $cost, float $margin): array
    {
        $sale_price = round($cost + $margin, 2);

        return [
            'profit_percentage' => $cost > 0 ? round(($margin / $cost) * 100, 2) : null,
            'sale_price'        => $sale_price,
        ];
    }
}
