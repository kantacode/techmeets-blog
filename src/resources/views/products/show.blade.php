@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <h1>{{ $product->name }}</h1>

    <p>価格：{{ number_format($product->price) }}円</p>
    <p>在庫：{{ $product->stock }}個</p>
    <p>カテゴリー：{{ $product->category }}</p>

    <div>{!! nl2br(e($product->description)) !!}</div>

    <p><a href="{{ route('products.edit', $product) }}">編集</a></p>

    <form action="{{ route('products.destroy', $product) }}" method="POST"
          onsubmit="return confirm('本当に削除しますか？');">
        @csrf
        @method('DELETE')
        <button type="submit">削除</button>
    </form>

    <p><a href="{{ route('products.index') }}">一覧に戻る</a></p>
@endsection
