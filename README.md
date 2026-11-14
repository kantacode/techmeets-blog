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
7. 画面のデザイン（CSS）を準備する（自分の PC で実行。Node.js が必要）
   ```bash
   cd src
   npm install
   npm run build
   cd ..
   ```
8. ブラウザで確認する
   - Laravel: http://localhost
   - phpMyAdmin: http://localhost:8080
   
## 機能一覧（Week 7）

### ブログ（/posts）
- 投稿の一覧表示（10件ごとのページネーション）
- 投稿の詳細表示
- 投稿の作成・編集・削除
- 入力チェック（タイトル・内容・カテゴリーは必須）

### 商品管理（/products）
- 商品の一覧・詳細・登録・編集・削除
- 価格・在庫数は0以上の整数のみ受け付ける

### イベント予約（/events, /reservations）
- イベントの一覧・詳細表示
- イベント詳細ページから予約（名前・メール・人数・日時）
- 予約の一覧表示とキャンセル
- ※イベントの登録画面はなし（phpMyAdmin で追加）

## テーブル定義

### posts
| カラム | 型 | 説明 |
|---|---|---|
| id | bigint | 主キー |
| title | varchar(200) | タイトル |
| content | text | 本文 |
| category | varchar(50) | カテゴリー |
| created_at / updated_at | timestamp | 作成・更新日時 |

### products
| カラム | 型 | 説明 |
|---|---|---|
| id | bigint | 主キー |
| name | varchar(100) | 商品名 |
| price | int | 価格 |
| description | text | 説明 |
| stock | int | 在庫数 |
| category | varchar(50) | カテゴリー |
| created_at / updated_at | timestamp | 作成・更新日時 |

### events
| カラム | 型 | 説明 |
|---|---|---|
| id | bigint | 主キー |
| title | varchar(100) | イベント名 |
| description | text | 説明 |
| event_date | datetime | 開催日時 |
| created_at / updated_at | timestamp | 作成・更新日時 |

### reservations
| カラム | 型 | 説明 |
|---|---|---|
| id | bigint | 主キー |
| event_id | bigint | events.id への外部キー（イベント削除時に予約も削除） |
| name | varchar(50) | 予約者名 |
| email | varchar(255) | メールアドレス |
| people | int | 人数 |
| reserved_at | datetime | 予約日時 |
| created_at / updated_at | timestamp | 作成・更新日時 |

## スクリーンショット

### 投稿一覧
![投稿一覧](docs/images/posts.png)

### 商品一覧
![商品一覧](docs/images/products.png)

### イベント詳細・予約フォーム
![イベント詳細](docs/images/event-show.png)

### 予約一覧
![予約一覧](docs/images/reservations.png)

## 機能一覧（Week 8）

### 会員機能（Laravel Breeze）
- 会員登録（/register）・ログイン（/login）・ログアウト
- プロフィール編集（/profile）

### 会員制ブログ
- 投稿の一覧・詳細は誰でも見られる
- 投稿の作成はログインした人だけ
- 投稿の編集・削除は、投稿した本人だけ（他人は 403）
- 一覧・詳細に投稿者名を表示

### コメント欄（投稿詳細ページ）
- コメントは誰でも見られる
- コメントの書き込みはログインした人だけ
- コメントの削除は、書いた本人だけ

## テーブル定義（Week 8 で追加・変更）

### posts（追加した列）
| カラム | 型 | 説明 |
|---|---|---|
| user_id | bigint | users.id への外部キー（投稿者） |

### comments
| カラム | 型 | 説明 |
|---|---|---|
| id | bigint | 主キー |
| post_id | bigint | posts.id への外部キー（投稿削除時にコメントも削除） |
| user_id | bigint | users.id への外部キー（書いた人） |
| body | text | コメント本文 |
| created_at / updated_at | timestamp | 作成・更新日時 |

## テストの実行

```bash
docker compose exec app php artisan test
```

セキュリティテストだけを実行する場合：

```bash
docker compose exec app php artisan test --filter=SecurityTest
```