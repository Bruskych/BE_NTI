<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблица журнала экспортов: exports_log.
 * Фиксирует все операции экспорта данных администраторами.
 */
return new class extends Migration
{
    /**
     * Создаёт таблицу exports_log для аудита экспортных операций.
     */
    public function up(): void
    {
        Schema::create('exports_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('export_type')->nullable();
            $table->json('filters_json')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Удаляет таблицу exports_log.
     */
    public function down(): void
    {
        Schema::dropIfExists('exports_log');
    }
};
