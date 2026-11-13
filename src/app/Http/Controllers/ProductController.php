<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // 一覧：GET /products
    public function index()
    {
        $products = Product::latest()->paginate(10);
        return view('products.index', compact('products'));
    }

    // 作成フォーム：GET /products/create
    public function create()
    {
        return view('products.create');
    }

    // 保存：POST /products
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|max:100',
            'price'       => 'required|integer|min:0',
            'description' => 'required',
            'stock'       => 'required|integer|min:0',
            'category'    => 'required|max:50',
        ]);

        $product = Product::create($validated);

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
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'        => 'required|max:100',
            'price'       => 'required|integer|min:0',
            'description' => 'required',
            'stock'       => 'required|integer|min:0',
            'category'    => 'required|max:50',
        ]);

        $product->update($validated);

        return redirect()->route('products.show', $product)
            ->with('success', '商品を更新しました');
    }

    // 削除：DELETE /products/{product}
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', '商品を削除しました');
    }
}