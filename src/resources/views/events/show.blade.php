@extends('layouts.app')

@section('title', $event->title)

@section('content')
    <h1>{{ $event->title }}</h1>
    <p>開催日：{{ $event->event_date->format('Y年m月d日 H:i') }}</p>
    <div>{!! nl2br(e($event->description)) !!}</div>

    <h2>このイベントを予約する</h2>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('reservations.store') }}" method="POST">
        @csrf
        <input type="hidden" name="event_id" value="{{ $event->id }}">

        <p>
            <label>お名前</label><br>
            <input type="text" name="name" value="{{ old('name') }}">
        </p>

        <p>
            <label>メールアドレス</label><br>
            <input type="email" name="email" value="{{ old('email') }}">
        </p>

        <p>
            <label>人数</label><br>
            <input type="number" name="people" min="1" value="{{ old('people', 1) }}">
        </p>

        <p>
            <label>日時</label><br>
            <input type="datetime-local" name="reserved_at" value="{{ old('reserved_at') }}">
        </p>

        <button type="submit">予約する</button>
    </form>

    <p><a href="{{ route('events.index') }}">イベント一覧に戻る</a></p>
@endsection