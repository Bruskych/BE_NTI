<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблица программ: Программа А (гранты) и Программа Б (практика).
 * Является корневой сущностью для конкурсов, заявок и шаблонов оценки.
 */
return new class extends Migration
{
    /**
     * Создаёт таблицу programs с типами grant (А) и practice (Б).
     */
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->enum('type', ['grant', 'practice'])->nullable(); // grant = Program A, practice = Program B
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Удаляет таблицу programs.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
