<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблица специализаций и сводные таблицы для связи с вызовами и челленджами.
 * Специализации используются для фильтрации и подбора команд по направлениям.
 */
return new class extends Migration
{
    /**
     * Создаёт таблицу specializations и сводные таблицы call_specialization, challenge_specialization.
     */
    public function up(): void
    {
        // Справочник специализаций (Frontend, Backend, AI и т.д.)
        Schema::create('specializations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Специализации, требуемые для конкурсного вызова (многие-ко-многим)
        Schema::create('call_specialization', function (Blueprint $table) {
            $table->foreignId('call_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialization_id')->constrained()->cascadeOnDelete();
            $table->primary(['call_id', 'specialization_id']);
        });

        // Специализации, требуемые для челленджа (многие-ко-многим)
        Schema::create('challenge_specialization', function (Blueprint $table) {
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialization_id')->constrained()->cascadeOnDelete();
            $table->primary(['challenge_id', 'specialization_id']);
        });
    }

    /**
     * Удаляет сводные таблицы и таблицу specializations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_specialization');
        Schema::dropIfExists('call_specialization');
        Schema::dropIfExists('specializations');
    }
};
