@extends('layouts.app')

@section('title', '商品一覧')

@section('content')
    <h1>商品一覧</h1>

    <p><a href="{{ route('products.create') }}">商品を登録する</a></p>

    @forelse ($products as $product)
        <article>
            <h2><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h2>
            <p>価格：{{ number_format($product->price) }}円 ／ 在庫：{{ $product->stock }}個</p>
            <p>カテゴリー：{{ $product->category }}</p>
        </article>
        <hr>
    @empty
        <p>商品がありません</p>
    @endforelse

    {{ $products->links('pagination::default') }}
@endsection
