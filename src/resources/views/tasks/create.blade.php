@extends('layouts.blog')

@section('title', 'タスク追加')

@section('content')
    <h1>タスク追加</h1>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('tasks.store') }}" method="POST">
        @csrf

        <p>
            <label>タイトル</label><br>
            <input type="text" name="title" value="{{ old('title') }}">
        </p>

        <p>
            <label>期限（なくてもOK）</label><br>
            <input type="date" name="due_date" value="{{ old('due_date') }}">
        </p>

        <p>
            <label>メモ（なくてもOK）</label><br>
            <textarea name="description" rows="4" cols="50">{{ old('description') }}</textarea>
        </p>

        <button type="submit">追加する</button>
    </form>

    <p><a href="{{ route('tasks.index') }}">一覧に戻る</a></p>
@endsection