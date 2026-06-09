<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблицы поддержки проекта: mentorships, milestones, consultations.
 * Ментор закрепляется за проектом, ведёт консультации по этапам разработки.
 */
return new class extends Migration
{
    /**
     * Создаёт таблицы менторства, этапов проекта и консультаций.
     */
    public function up(): void
    {
        // Менторство: закрепление ментора за проектом
        Schema::create('mentorships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mentor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 100)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Этапы разработки с дедлайнами и процентом выполнения
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->string('status', 100)->nullable();
            $table->integer('completion_percentage')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Консультации ментора по конкретному этапу проекта
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentorship_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mentor_id')->nullable()->constrained('users')->nullOnDelete();
            // Связь с этапом для отчётности о прогрессе
            $table->foreignId('milestone_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->text('recommendations')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Удаляет таблицы consultations, milestones и mentorships.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
        Schema::dropIfExists('milestones');
        Schema::dropIfExists('mentorships');
    }
};
