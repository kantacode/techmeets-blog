@extends('layouts.app')

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

    <form action="{{ route('posts.update', $post) }}" method="POST">
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

        <button type="submit">更新する</button>
    </form>
@endsection
