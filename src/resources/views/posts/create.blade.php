@extends('layouts.blog')

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

    {{-- ファイルを送るときは enctype="multipart/form-data" が必要 --}}
    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
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

        <p>
            <label>画像（任意・5MB まで）</label><br>
            <input type="file" name="image" accept="image/*">
        </p>

        <button type="submit">投稿する</button>
    </form>
@endsection
