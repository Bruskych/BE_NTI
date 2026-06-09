<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблицы GDPR и аудита: gdpr_consents и audit_events.
 * Обеспечивает соответствие требованиям GDPR и журналирование действий.
 */
return new class extends Migration
{
    /**
     * Создаёт таблицы согласий GDPR и журнала аудита действий пользователей.
     */
    public function up(): void
    {
        // Согласия пользователей с политиками конфиденциальности
        Schema::create('gdpr_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('consent_type')->nullable();
            $table->string('version', 50)->nullable();
            $table->timestamp('accepted_at');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();
        });

        // Журнал аудита: все значимые действия в системе
        Schema::create('audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action')->nullable();
            $table->string('object_type')->nullable();
            $table->unsignedBigInteger('object_id')->nullable();
            $table->json('old_values_json')->nullable();
            $table->json('new_values_json')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('result')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Удаляет таблицы audit_events и gdpr_consents.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('gdpr_consents');
    }
};
