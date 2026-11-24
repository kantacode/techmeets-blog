<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <p>{{ $userName }} さん</p>

    <p>TechMeets Blog へのご登録ありがとうございます。</p>

    <p>さっそく記事を書いたり、気に入った記事の書き手を応援したりしてみてください。</p>

    <p><a href="{{ route('posts.index') }}">投稿一覧を見る</a></p>

    <p>TechMeets Blog</p>
</body>
</html>
