<?php

/**
 * CORS（Cross-Origin Resource Sharing）の設定
 * フロントエンド（localhost:5173）とバックエンド（localhost:80）はオリジンが違うので、
 * ここで許可しないとブラウザが API の結果を読ませてくれない
 */
return [

    'paths' => ['api/*'],   // API ルート全体に適用

    'allowed_methods' => ['*'],

    // API を呼んでよいフロントエンドの URL（* にせず、明示する）
    'allowed_origins' => array_filter([
        'http://localhost:5173',     // Vite（Vue.js）の開発サーバー
        env('FRONTEND_URL'),         // 本番など、別の URL を足したいときは .env に書く
    ]),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
