<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблица проектов — создаётся для одобренных заявок.
 * Проект является рабочей фазой после одобрения заявки командой.
 */
return new class extends Migration
{
    /**
     * Создаёт таблицу projects с привязкой один-к-одному к заявке.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 100)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->decimal('final_score', 6, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Удаляет таблицу projects.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
