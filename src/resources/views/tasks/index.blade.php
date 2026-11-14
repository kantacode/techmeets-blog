@extends('layouts.blog')

@section('title', 'タスク一覧')

@section('content')
    <h1>タスク一覧</h1>

    <p><a href="{{ route('tasks.create') }}">タスクを追加する</a></p>

    @forelse ($tasks as $task)
        <article>
            <h2 style="{{ $task->is_done ? 'text-decoration: line-through; color: gray;' : '' }}">
                {{ $task->is_done ? '✅' : '⬜' }} {{ $task->title }}
            </h2>

            @if ($task->due_date)
                <p>期限：{{ $task->due_date->format('Y年m月d日') }}</p>
            @endif

            @if ($task->description)
                <p>{!! nl2br(e($task->description)) !!}</p>
            @endif

            {{-- 完了 ⇔ 未完了 の切り替えボタン --}}
            <form action="{{ route('tasks.toggle', $task) }}" method="POST" style="display: inline;">
                @csrf
                @method('PATCH')
                <button type="submit">{{ $task->is_done ? '未完了に戻す' : '完了にする' }}</button>
            </form>

            <a href="{{ route('tasks.edit', $task) }}">編集</a>

            <form action="{{ route('tasks.destroy', $task) }}" method="POST" style="display: inline;"
                  onsubmit="return confirm('このタスクを削除しますか？');">
                @csrf
                @method('DELETE')
                <button type="submit">削除</button>
            </form>
        </article>
        <hr>
    @empty
        <p>タスクはありません</p>
    @endforelse

    {{ $tasks->links('pagination::default') }}
@endsection