<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблицы шаблонов оценки и критериев.
 * Шаблон определяет набор взвешенных критериев для оценки заявок в рамках программы.
 */
return new class extends Migration
{
    /**
     * Создаёт таблицы evaluation_templates и evaluation_criteria.
     */
    public function up(): void
    {
        // Шаблон оценивания — привязан к программе
        Schema::create('evaluation_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Критерии оценивания внутри шаблона (с весами для взвешенной суммы баллов)
        Schema::create('evaluation_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')
                  ->constrained('evaluation_templates')
                  ->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->integer('order')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Удаляет таблицы evaluation_criteria и evaluation_templates.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_criteria');
        Schema::dropIfExists('evaluation_templates');
    }
};
