@extends('layouts.blog')

@section('title', $post->title)

@section('content')
    <h1>{{ $post->title }}</h1>
    <p>カテゴリー：{{ $post->category }}</p>
    <p>投稿者：{{ $post->user->name }}</p>
    <small>{{ $post->created_at->format('Y年m月d日 H:i') }}</small>

    {{-- S3 に保存した画像（画像がある投稿だけ表示） --}}
    @if ($post->image_url)
        <p>
            <img src="{{ $post->image_url }}" alt="{{ $post->title }}" style="max-width: 100%; height: auto;">
        </p>
    @endif

    <div>{!! nl2br(e($post->content)) !!}</div>

    {{-- いいね（Week 14）：数は誰でも見られる。押せるのはログインした人だけ --}}
    <p>いいね {{ $post->liked_users_count }}</p>
    @auth
        <form method="POST" action="{{ route('posts.like', $post) }}">
            @csrf
            <button type="submit">{{ $post->isLikedBy(auth()->user()) ? 'いいねを取り消す' : 'いいね' }}</button>
        </form>
    @endauth

    {{-- 投げ銭（Stripe のテスト決済）：ログインした人だけ --}}
    @auth
        @error('payment')
            <p style="color: red;">{{ $message }}</p>
        @enderror

        <form method="POST" action="{{ route('supports.create', $post) }}">
            @csrf
            <button type="submit">この記事の書き手を応援する（500円）</button>
        </form>
    @endauth

    {{-- 編集・削除ボタンは、投稿した本人にだけ表示する --}}
    @if (auth()->id() === $post->user_id)
        <p>
            <a href="{{ route('posts.edit', $post) }}">編集</a>
        </p>

        <form action="{{ route('posts.destroy', $post) }}" method="POST"
              onsubmit="return confirm('本当に削除しますか？');">
            @csrf
            @method('DELETE')
            <button type="submit">削除</button>
        </form>
    @endif

    <hr>

    {{-- コメント一覧（ログインしていなくても見られる） --}}
    <h2>コメント（{{ $post->comments->count() }}件）</h2>

    @forelse ($post->comments as $comment)
        <div>
            <p>
                <strong>{{ $comment->user->name }}</strong>
                <small>{{ $comment->created_at->format('Y年m月d日 H:i') }}</small>
            </p>
            <p>{!! nl2br(e($comment->body)) !!}</p>

            {{-- 削除ボタンは、コメントを書いた本人にだけ表示する --}}
            @if (auth()->id() === $comment->user_id)
                <form action="{{ route('comments.destroy', $comment) }}" method="POST"
                      onsubmit="return confirm('このコメントを削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <button type="submit">削除</button>
                </form>
            @endif
        </div>
    @empty
        <p>まだコメントはありません</p>
    @endforelse

    {{-- 書き込みフォーム（ログインした人だけ） --}}
    @auth
        <h3>コメントを書く</h3>

        @error('body')
            <p style="color: red;">{{ $message }}</p>
        @enderror

        <form action="{{ route('comments.store', $post) }}" method="POST">
            @csrf
            <textarea name="body" rows="4" cols="50">{{ old('body') }}</textarea>
            <br>
            <button type="submit">コメントする</button>
        </form>
    @else
        <p>コメントするには <a href="{{ route('login') }}">ログイン</a> してください。</p>
    @endauth

    <p><a href="{{ route('posts.index') }}">一覧に戻る</a></p>
@endsection
