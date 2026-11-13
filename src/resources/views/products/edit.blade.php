@extends('layouts.app')

@section('title', '商品編集')

@section('content')
    <h1>商品編集</h1>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('products.update', $product) }}" method="POST">
        @csrf
        @method('PUT')

        <p>
            <label>商品名</label><br>
            <input type="text" name="name" value="{{ old('name', $product->name) }}">
        </p>

        <p>
            <label>価格（円）</label><br>
            <input type="number" name="price" min="0" value="{{ old('price', $product->price) }}">
        </p>

        <p>
            <label>在庫数</label><br>
            <input type="number" name="stock" min="0" value="{{ old('stock', $product->stock) }}">
        </p>

        <p>
            <label>カテゴリー</label><br>
            <input type="text" name="category" value="{{ old('category', $product->category) }}">
        </p>

        <p>
            <label>説明</label><br>
            <textarea name="description" rows="6" cols="50">{{ old('description', $product->description) }}</textarea>
        </p>

        <button type="submit">更新する</button>
    </form>
@endsection
