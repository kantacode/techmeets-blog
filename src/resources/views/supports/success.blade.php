@extends('layouts.blog')

@section('title', '応援ありがとうございました')

@section('content')
    <h1>応援ありがとうございました！</h1>

    <p>「{{ $support->post->title }}」の書き手を {{ number_format($support->amount) }} 円で応援しました。</p>

    <p><a href="{{ route('posts.show', $support->post) }}">記事に戻る</a></p>
    <p><a href="{{ route('posts.index') }}">一覧に戻る</a></p>
@endsection
