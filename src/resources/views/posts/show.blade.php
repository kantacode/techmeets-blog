@extends('layouts.app')

@section('title', $post->title)

@section('content')
    <h1>{{ $post->title }}</h1>
    <p>カテゴリー：{{ $post->category }}</p>
    <small>{{ $post->created_at->format('Y年m月d日 H:i') }}</small>

    <div>{!! nl2br(e($post->content)) !!}</div>

    <p>
        <a href="{{ route('posts.edit', $post) }}">編集</a>
    </p>

    <form action="{{ route('posts.destroy', $post) }}" method="POST"
          onsubmit="return confirm('本当に削除しますか？');">
        @csrf
        @method('DELETE')
        <button type="submit">削除</button>
    </form>

    <p><a href="{{ route('posts.index') }}">一覧に戻る</a></p>
@endsection
