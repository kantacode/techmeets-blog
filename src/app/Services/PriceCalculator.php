<?php

namespace App\Services;

/**
 * 税込合計と割引後の価格を計算する（Week 14：ユニットテストの練習用）
 * DB も HTTP も使わないので、PHPUnit の TestCase で new するだけでテストできる
 */
class PriceCalculator
{
    public function calculateTotal(int $price, int $quantity, float $taxRate = 0.1): int
    {
        if ($price < 0 || $quantity < 0) {
            throw new \InvalidArgumentException('Price and quantity must be positive');
        }

        $subtotal = $price * $quantity;
        $tax = $subtotal * $taxRate;

        return (int) ($subtotal + $tax);
    }

    public function applyDiscount(int $price, int $discountPercent): int
    {
        if ($discountPercent < 0 || $discountPercent > 100) {
            throw new \InvalidArgumentException('Discount must be between 0 and 100');
        }

        return (int) ($price * (1 - $discountPercent / 100));
    }
}
