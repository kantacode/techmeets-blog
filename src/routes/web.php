<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
      Route::resource('tasks', TaskController::class)->except(['show']);
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
});

// Week 7 のルート（Breeze に上書きされたので書き戻し）

// 投稿の作成・編集・削除はログインした人だけ
// ※ /posts/create を /posts/{post} より先に登録するため、こちらを上に書く
Route::middleware('auth')->group(function () {
    Route::resource('posts', PostController::class)->except(['index', 'show']);

    // コメントの書き込み・削除もログインした人だけ
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
});

// 投稿の一覧・詳細は誰でも見られる
Route::resource('posts', PostController::class)->only(['index', 'show']);

Route::resource('products', ProductController::class);
Route::resource('events', EventController::class)->only(['index', 'show']);
Route::resource('reservations', ReservationController::class)->only(['index', 'store', 'destroy']);

require __DIR__.'/auth.php';
