<?php

use App\Http\Controllers\Api\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ここに書いたルートには自動で /api が付く（例：/api/posts）。CSRF チェックはされない

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// 投稿 API（今週は認証なし）
Route::get('/posts', [PostController::class, 'index']);      // 一覧
Route::get('/posts/{id}', [PostController::class, 'show']);  // 1件取得
Route::post('/posts', [PostController::class, 'store']);     // 新規作成
