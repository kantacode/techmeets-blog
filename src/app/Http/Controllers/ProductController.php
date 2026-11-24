<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\ProductService;

/**
 * 商品の「受け取って、担当に渡して、画面を返す」だけを担当する（交通整理）
 *   入力チェック   → ProductRequest
 *   DB の読み書き → ProductRepository
 *   業務の手順     → ProductService
 */
class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private ProductRepository $productRepository
    ) {
    }

    // 一覧：GET /products
    public function index()
    {
        $products = $this->productRepository->getLatest();
        return view('products.index', compact('products'));
    }

    // 作成フォーム：GET /products/create
    public function create()
    {
        return view('products.create');
    }

    // 保存：POST /products
    // ProductRequest が先に入力チェックをしてくれるので、ここに来た時点でデータは正しい
    public function store(ProductRequest $request)
    {
        $product = $this->productService->createProduct($request->validated());

        return redirect()->route('products.show', $product)
            ->with('success', '商品を登録しました');
    }

    // 詳細：GET /products/{product}
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    // 編集フォーム：GET /products/{product}/edit
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    // 更新：PUT /products/{product}
    public function update(ProductRequest $request, Product $product)
    {
        $product = $this->productService->updateProduct($product, $request->validated());

        return redirect()->route('products.show', $product)
            ->with('success', '商品を更新しました');
    }

    // 削除：DELETE /products/{product}
    public function destroy(Product $product)
    {
        $this->productService->deleteProduct($product);

        return redirect()->route('products.index')
            ->with('success', '商品を削除しました');
    }
}
