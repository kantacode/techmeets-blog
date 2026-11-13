@extends('layouts.app')

@section('title', '予約一覧')

@section('content')
    <h1>予約一覧</h1>

    @forelse ($reservations as $reservation)
        <article>
            <h2>{{ $reservation->event->title }}</h2>
            <p>お名前：{{ $reservation->name }}</p>
            <p>メール：{{ $reservation->email }}</p>
            <p>人数：{{ $reservation->people }}名</p>
            <p>日時：{{ $reservation->reserved_at->format('Y年m月d日 H:i') }}</p>

            <form action="{{ route('reservations.destroy', $reservation) }}" method="POST"
                  onsubmit="return confirm('この予約をキャンセルしますか？');">
                @csrf
                @method('DELETE')
                <button type="submit">キャンセル</button>
            </form>
        </article>
        <hr>
    @empty
        <p>予約がありません</p>
    @endforelse

    {{ $reservations->links('pagination::default') }}
@endsection