@extends('layouts.blog')

@section('title', 'タスク編集')

@section('content')
    <h1>タスク編集</h1>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('tasks.update', $task) }}" method="POST">
        @csrf
        @method('PUT')

        <p>
            <label>タイトル</label><br>
            <input type="text" name="title" value="{{ old('title', $task->title) }}">
        </p>

        <p>
            <label>期限（なくてもOK）</label><br>
            <input type="date" name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
        </p>

        <p>
            <label>メモ（なくてもOK）</label><br>
            <textarea name="description" rows="4" cols="50">{{ old('description', $task->description) }}</textarea>
        </p>

        <button type="submit">更新する</button>
    </form>

    <p><a href="{{ route('tasks.index') }}">一覧に戻る</a></p>
@endsection