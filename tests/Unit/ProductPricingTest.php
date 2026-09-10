<?php

namespace Tests\Unit;

use App\Support\ProductPricing;
use PHPUnit\Framework\TestCase;

class ProductPricingTest extends TestCase
{
    public function test_from_cost_and_percentage_calculates_margin_and_sale(): void
    {
        $pricing = ProductPricing::fromCostAndPercentage(100, 20);

        $this->assertSame(120.0, $pricing['sale_price']);
        $this->assertSame(20.0, $pricing['profit_margin']);
    }

    public function test_from_cost_and_sale_calculates_percentage_and_margin(): void
    {
        $pricing = ProductPricing::fromCostAndSale(100, 150);

        $this->assertSame(50.0, $pricing['profit_percentage']);
        $this->assertSame(50.0, $pricing['profit_margin']);
    }

    public function test_from_cost_and_margin_calculates_percentage_and_sale(): void
    {
        $pricing = ProductPricing::fromCostAndMargin(80, 20);

        $this->assertSame(25.0, $pricing['profit_percentage']);
        $this->assertSame(100.0, $pricing['sale_price']);
    }

    public function test_from_cost_and_sale_skips_percentage_when_cost_is_zero(): void
    {
        $pricing = ProductPricing::fromCostAndSale(0, 40);

        $this->assertNull($pricing['profit_percentage']);
        $this->assertSame(40.0, $pricing['profit_margin']);
    }
}
