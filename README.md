# techmeets-blog

Docker で動く Laravel 開発環境です。

## セットアップ手順

1. Docker Desktop を起動する
2. `.env.example` をコピーして `.env` を作る
3. コンテナを起動する
   ```bash
   docker compose up -d --build
   ```
4. Laravel のライブラリを入れる
   ```bash
   docker compose exec app composer install
   ```
5. `src/.env.example` をコピーして `src/.env` を作り、DB 設定を書く
   ```
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=laravel
   DB_USERNAME=root
   DB_PASSWORD=secret
   ```
6. アプリキーを作ってマイグレーションする
   ```bash
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate
   ```
7. ブラウザで確認する
   - Laravel: http://localhost
   - phpMyAdmin: http://localhost:8080