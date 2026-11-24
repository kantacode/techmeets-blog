<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Week 14（TDD）：投稿への「いいね」
     * users と posts の多対多をつなぐ中間テーブル
     */
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // 誰が
            $table->foreignId('post_id')->constrained()->cascadeOnDelete(); // どの投稿に
            $table->timestamps();

            $table->unique(['user_id', 'post_id']); // 同じ人が同じ投稿に2回いいねできないようにする
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
