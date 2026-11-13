@extends('layouts.app')

@section('title', '新規投稿')

@section('content')
    <h1>新規投稿</h1>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('posts.store') }}" method="POST">
        @csrf

        <p>
            <label>タイトル</label><br>
            <input type="text" name="title" value="{{ old('title') }}">
        </p>

        <p>
            <label>カテゴリー</label><br>
            <input type="text" name="category" value="{{ old('category') }}">
        </p>

        <p>
            <label>内容</label><br>
            <textarea name="content" rows="8" cols="50">{{ old('content') }}</textarea>
        </p>

        <button type="submit">投稿する</button>
    </form>
@endsection
