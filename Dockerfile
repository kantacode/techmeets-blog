# =============================================================
# 本番用の Dockerfile（Week 16：マルチステージビルド）
# 開発用は docker/php/Dockerfile のまま残している
#
# ステージ1 composer-builder : PHP のライブラリ（vendor）を入れる
# ステージ2 node-builder     : CSS・JS をビルドする（public/build）
# ステージ3 web              : Nginx 用のイメージ（public/ を焼き込む）
# ステージ4 app（最終）       : PHP-FPM 用のイメージ。上のステージから必要な物だけコピーする
# =============================================================

# ---------- ステージ1：PHP のライブラリ ----------
FROM composer:2 AS composer-builder
WORKDIR /app
# composer.json と composer.lock が変わらない限り、ここはキャッシュが使われる
COPY src/composer.json src/composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction --prefer-dist

# ---------- ステージ2：CSS・JS のビルド ----------
FROM node:22-alpine AS node-builder
WORKDIR /app
COPY src/package.json src/package-lock.json ./
RUN npm ci
COPY src/resources ./resources
# Vite・Tailwind・PostCSS の設定ファイル（Breeze が作ったもの）
COPY src/vite.config.js src/tailwind.config.js src/postcss.config.js ./
# Tailwind はページネーションの Blade も読むので、そのファイルだけ vendor から持ってくる
COPY --from=composer-builder /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

# ---------- ステージ3：Nginx 用（public/ とビルド済みの CSS・JS を焼き込む） ----------
FROM nginx:alpine AS web
COPY src/public /var/www/html/public
COPY --from=node-builder /app/public/build /var/www/html/public/build

# ---------- ステージ4：PHP-FPM 用（最終イメージ） ----------
FROM php:8.2-fpm-alpine AS app
WORKDIR /var/www/html
# OS の部品を最新にして、修正済みの脆弱性を取り込む（Trivy で見つかった libxml2 など）
RUN apk upgrade --no-cache

# 必要な拡張だけ入れる（RDS につなぐ pdo_mysql と、PHP を速くする opcache）
# fcgi はヘルスチェックで php-fpm に問い合わせるためのコマンド
RUN apk add --no-cache fcgi \
    && docker-php-ext-install pdo_mysql opcache \
    # php-fpm の ping 機能を有効にする（/ping に問い合わせると pong が返る）
    && echo "ping.path = /ping" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    # 本番用の php.ini を使い、アップロードの上限は開発と同じ 10MB にする
    && cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
    && printf "upload_max_filesize=10M\npost_max_size=10M\n" > /usr/local/etc/php/conf.d/uploads.ini \
    # タイムゾーンを日本時間にする
    && apk add --no-cache tzdata && cp /usr/share/zoneinfo/Asia/Tokyo /etc/localtime && apk del tzdata

# ライブラリとビルド済みの CSS・JS を、上のステージからコピーする（Composer や Node.js 自体は入れない）
COPY --chown=www-data:www-data --from=composer-builder /app/vendor ./vendor
COPY --chown=www-data:www-data --from=node-builder /app/public/build ./public/build
# アプリのコード（src/ の中身）。.env・tests・node_modules などは .dockerignore で除外している
COPY --chown=www-data:www-data src/ .

# Laravel が書き込むフォルダの用意（ログ・キャッシュ・セッションなど）
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# root ではなく www-data（Alpine の PHP イメージに最初からいる一般ユーザー）で動かす
USER www-data

# php-fpm（9000番）の /ping に問い合わせ、pong が返れば正常
HEALTHCHECK --interval=30s --timeout=3s --retries=3 \
  CMD SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET \
      cgi-fcgi -bind -connect 127.0.0.1:9000 | grep -q pong || exit 1

CMD ["php-fpm"]
