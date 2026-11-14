<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>@yield('title') - TechMeets Blog</title>
</head>
<body>
    <header>
        <nav>
            <a href="{{ route('posts.index') }}">投稿一覧</a>
            <a href="{{ route('posts.create') }}">新規投稿</a>
            <a href="{{ route('products.index') }}">商品一覧</a>
             <a href="{{ route('events.index') }}">イベント一覧</a>
            <a href="{{ route('reservations.index') }}">予約一覧</a>
        </nav>
    </header>

    <main>
        @if (session('success'))
            <p style="color: green;">{{ session('success') }}</p>
        @endif

        @yield('content')
    </main>

    <footer>
        <p>© 2026 TechMeets Blog</p>
    </footer>
</body>
</html>
