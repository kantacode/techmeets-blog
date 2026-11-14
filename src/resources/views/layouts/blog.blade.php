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
            @auth
                <a href="{{ route('posts.create') }}">新規投稿</a>
            @endauth
            <a href="{{ route('products.index') }}">商品一覧</a>
            <a href="{{ route('events.index') }}">イベント一覧</a>
            <a href="{{ route('reservations.index') }}">予約一覧</a>
        </nav>

        {{-- ログイン状態の表示 --}}
        <div>
            @auth
                <span>{{ auth()->user()->name }} さんでログイン中</span>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit">ログアウト</button>
                </form>
            @else
                <a href="{{ route('login') }}">ログイン</a>
                <a href="{{ route('register') }}">会員登録</a>
            @endauth
        </div>
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
