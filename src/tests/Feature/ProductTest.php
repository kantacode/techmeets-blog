<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Week 7 の商品管理（/products）の Feature Test
 */
class ProductTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name'        => 'テスト商品',
            'price'       => 1000,
            'description' => '説明',
            'stock'       => 5,
            'category'    => '文房具',
        ], $overrides);
    }

    private function makeProduct(array $overrides = []): Product
    {
        return Product::create($this->validData($overrides));
    }

    public function test_product_list_is_displayed(): void
    {
        $this->makeProduct(['name' => '一覧に出る商品']);

        $this->get('/products')->assertOk()->assertSee('一覧に出る商品');
    }

    public function test_product_detail_is_displayed(): void
    {
        $product = $this->makeProduct(['name' => '詳細の商品']);

        $this->get("/products/{$product->id}")->assertOk()->assertSee('詳細の商品');
    }

    public function test_create_and_edit_forms_are_displayed(): void
    {
        $product = $this->makeProduct();

        $this->get('/products/create')->assertOk();
        $this->get("/products/{$product->id}/edit")->assertOk();
    }

    public function test_product_can_be_created(): void
    {
        $response = $this->post('/products', $this->validData());

        $product = Product::first();
        $response->assertRedirect(route('products.show', $product));
        $this->assertDatabaseHas('products', ['name' => 'テスト商品', 'price' => 1000]);
    }

    public function test_product_can_be_updated(): void
    {
        $product = $this->makeProduct();

        $response = $this->put("/products/{$product->id}", $this->validData(['price' => 1500]));

        $response->assertRedirect(route('products.show', $product));
        $this->assertSame(1500, $product->fresh()->price);
    }

    public function test_product_can_be_deleted(): void
    {
        $product = $this->makeProduct();

        $response = $this->delete("/products/{$product->id}");

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_required_fields_are_validated(): void
    {
        $response = $this->post('/products', []);

        $response->assertSessionHasErrors(['name', 'price', 'description', 'stock', 'category']);
    }

    // 境界値：価格0円（下限ちょうど）→ OK / -1円 → NG
    public function test_price_of_zero_is_accepted(): void
    {
        $this->post('/products', $this->validData(['price' => 0]))->assertSessionHasNoErrors();
    }

    public function test_negative_price_is_rejected(): void
    {
        $this->post('/products', $this->validData(['price' => -1]))->assertSessionHasErrors('price');
    }

    // 境界値：在庫0（下限ちょうど）→ OK / -1 → NG
    public function test_stock_of_zero_is_accepted(): void
    {
        $this->post('/products', $this->validData(['stock' => 0]))->assertSessionHasNoErrors();
    }

    public function test_negative_stock_is_rejected(): void
    {
        $this->post('/products', $this->validData(['stock' => -1]))->assertSessionHasErrors('stock');
    }

    public function test_price_must_be_integer(): void
    {
        $this->post('/products', $this->validData(['price' => '千円']))->assertSessionHasErrors('price');
    }
}
