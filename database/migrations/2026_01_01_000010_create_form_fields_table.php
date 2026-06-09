<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблица полей анкеты заявки (конструктор форм).
 * Позволяет администратору настраивать поля формы для каждой программы или вызова.
 */
return new class extends Migration
{
    /**
     * Создаёт таблицу form_fields для динамических полей анкеты.
     */
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained();
            // null = платné для целой программы; not null = специфично для данного вызова
            $table->foreignId('call_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('label')->nullable();
            $table->string('type', 100)->nullable();
            $table->boolean('required')->default(false);
            $table->json('options_json')->nullable();
            $table->text('validation_rules')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Удаляет таблицу form_fields.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
