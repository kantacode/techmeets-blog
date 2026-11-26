<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Week 17：投稿一覧の「新しい順に10件」（ORDER BY created_at DESC LIMIT 10）を速くする
     * インデックスがないと、全部の投稿を読んで並べ替えてから10件を選ぶ（type: ALL、Using filesort）
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};
