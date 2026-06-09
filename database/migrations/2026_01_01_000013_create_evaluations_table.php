<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблицы оценок заявок: evaluations и evaluation_scores.
 * Каждая заявка может иметь несколько оценок от разных экспертов.
 */
return new class extends Migration
{
    /**
     * Создаёт таблицы evaluations (оценка эксперта) и evaluation_scores (баллы по критериям).
     */
    public function up(): void
    {
        // Оценка заявки от одного эксперта с итоговым баллом и рекомендацией
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('total_score', 6, 2)->nullable();
            $table->text('comment')->nullable();
            $table->string('recommendation', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Баллы по отдельным критериям оценивания
        Schema::create('evaluation_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('criteria_id')
                  ->constrained('evaluation_criteria')
                  ->cascadeOnDelete();
            $table->decimal('score', 6, 2)->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Удаляет таблицы evaluation_scores и evaluations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_scores');
        Schema::dropIfExists('evaluations');
    }
};
