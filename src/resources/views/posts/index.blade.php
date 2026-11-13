@extends('layouts.app')

@section('title', '投稿一覧')

@section('content')
    <h1>投稿一覧</h1>

    @forelse ($posts as $post)
        <article>
            <h2><a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a></h2>
            <p>カテゴリー：{{ $post->category }}</p>
            <p>{{ Str::limit($post->content, 100) }}</p>
            <small>{{ $post->created_at->format('Y年m月d日') }}</small>
        </article>
        <hr>
    @empty
        <p>投稿がありません</p>
    @endforelse

    {{ $posts->links('pagination::default') }}
@endsection
