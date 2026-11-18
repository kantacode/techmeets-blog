@extends('layouts.blog')

@section('title', '投稿編集')

@section('content')
    <h1>投稿編集</h1>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    {{-- ファイルを送るときは enctype="multipart/form-data" が必要 --}}
    <form action="{{ route('posts.update', $post) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <p>
            <label>タイトル</label><br>
            <input type="text" name="title" value="{{ old('title', $post->title) }}">
        </p>

        <p>
            <label>カテゴリー</label><br>
            <input type="text" name="category" value="{{ old('category', $post->category) }}">
        </p>

        <p>
            <label>内容</label><br>
            <textarea name="content" rows="8" cols="50">{{ old('content', $post->content) }}</textarea>
        </p>

        <p>
            <label>画像（任意・5MB まで。選ぶと今の画像と差し替わります）</label><br>
            @if ($post->image_url)
                <img src="{{ $post->image_url }}" alt="現在の画像" style="max-width: 200px; display: block; margin-bottom: 8px;">
            @endif
            <input type="file" name="image" accept="image/*">
        </p>

        <button type="submit">更新する</button>
    </form>
@endsection
