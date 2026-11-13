@extends('layouts.app')

@section('title', 'イベント一覧')

@section('content')
    <h1>イベント一覧</h1>

    @forelse ($events as $event)
        <article>
            <h2><a href="{{ route('events.show', $event) }}">{{ $event->title }}</a></h2>
            <p>開催日：{{ $event->event_date->format('Y年m月d日 H:i') }}</p>
        </article>
        <hr>
    @empty
        <p>イベントがありません</p>
    @endforelse

    {{ $events->links('pagination::default') }}
@endsection