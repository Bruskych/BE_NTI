<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Добавляет колонку type в таблицу posts.
 * Позволяет разделить контент на статьи, FAQ и истории успеха (spec 6.1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Тип публикации: article — новость/блог, faq — вопрос-ответ, success_story — история успеха
            $table->enum('type', ['article', 'faq', 'success_story'])->default('article')->after('author_id');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
