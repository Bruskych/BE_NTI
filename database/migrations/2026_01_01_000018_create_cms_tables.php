<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблицы CMS: posts (новости/блог), pages (статические страницы), partners (партнёры).
 * Обеспечивает публичный контент платформы для SEO и информирования пользователей.
 */
return new class extends Migration
{
    /**
     * Создаёт таблицы CMS: посты, страницы и партнёры.
     */
    public function up(): void
    {
        // Публикации / новости блога
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('slug')->unique()->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('featured_image', 500)->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image', 500)->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Статические страницы (About, Privacy Policy, Terms of Use)
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('slug')->unique()->nullable();
            $table->longText('content')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Партнёры платформы с логотипами и ссылками на сайты
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('logo_path', 500)->nullable();
            $table->string('website_link', 500)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Удаляет таблицы partners, pages и posts.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('posts');
    }
};
