<?php

namespace Tests\Unit;

use App\Services\PriceCalculator;
use PHPUnit\Framework\TestCase;

/**
 * PriceCalculator の正常系・異常系（境界値は PriceCalculatorBoundaryTest）
 * DB を使わないので、PHPUnit の TestCase で new するだけで動く
 */
class PriceCalculatorTest extends TestCase
{
    private PriceCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new PriceCalculator();
    }

    // 正常系：税率0%なら 価格 × 数量 がそのまま返る
    public function test_calculate_total_without_tax(): void
    {
        $result = $this->calculator->calculateTotal(100, 2, 0);

        $this->assertSame(200, $result);
    }

    // 正常系：税率10%なら税込の合計が返る
    public function test_calculate_total_with_tax(): void
    {
        $result = $this->calculator->calculateTotal(100, 2, 0.1);

        $this->assertSame(220, $result);
    }

    // 正常系：税率を省略すると10%で計算される
    public function test_calculate_total_uses_ten_percent_tax_by_default(): void
    {
        $result = $this->calculator->calculateTotal(1000, 1);

        $this->assertSame(1100, $result);
    }

    // 境界値：数量0は有効（合計0円）
    public function test_calculate_total_with_zero_quantity(): void
    {
        $result = $this->calculator->calculateTotal(1000, 0);

        $this->assertSame(0, $result);
    }

    // 異常系：価格がマイナスなら例外
    public function test_calculate_total_throws_exception_for_negative_price(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Price and quantity must be positive');

        $this->calculator->calculateTotal(-100, 2);
    }

    // 異常系：数量がマイナスなら例外
    public function test_calculate_total_throws_exception_for_negative_quantity(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->calculator->calculateTotal(100, -1);
    }

    // 正常系：20%割引で 1000円 → 800円
    public function test_apply_discount(): void
    {
        $result = $this->calculator->applyDiscount(1000, 20);

        $this->assertSame(800, $result);
    }

    // 異常系：150%という割引率は例外
    public function test_apply_discount_throws_exception_for_invalid_percent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Discount must be between 0 and 100');

        $this->calculator->applyDiscount(1000, 150);
    }
}
