<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблица конкурсных вызовов (Calls) — Программа А.
 * Вызов открывает временное окно для приёма заявок с определённым бюджетом и дедлайном.
 * Жизненный цикл статуса: draft → open → closed.
 */
return new class extends Migration
{
    /**
     * Создаёт таблицу calls с привязкой к программе и шаблону оценки.
     */
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->enum('status', ['draft', 'open', 'closed'])->default('draft');
            $table->decimal('budget', 12, 2)->nullable();
            $table->foreignId('evaluation_template_id')
                  ->nullable()
                  ->constrained('evaluation_templates')
                  ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Удаляет таблицу calls.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
