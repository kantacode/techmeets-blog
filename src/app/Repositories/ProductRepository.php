<?php

namespace App\Repositories;

use App\Models\Product;

/**
 * 商品の「DB の読み書き」だけを担当する（倉庫係）
 */
class ProductRepository
{
    // 新しい順に10件ずつ
    public function getLatest()
    {
        return Product::latest()->paginate(10);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product;
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}