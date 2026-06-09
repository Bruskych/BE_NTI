<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблицы уведомлений: notifications, notification_preferences, email_templates, bulk_messages.
 * Обеспечивает систему оповещений пользователей через email и системные каналы.
 */
return new class extends Migration
{
    /**
     * Создаёт таблицы уведомлений, настроек, шаблонов писем и массовых рассылок.
     */
    public function up(): void
    {
        // Системные и email-уведомления для пользователей
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->nullable();
            $table->enum('channel', ['email', 'system', 'push'])->default('system');
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->json('data_json')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Индивидуальные настройки каналов уведомлений (один-к-одному с пользователем)
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('email_enabled')->default(true);
            $table->boolean('system_enabled')->default(true);
            $table->boolean('marketing_enabled')->default(false);
            $table->boolean('deadline_alerts_enabled')->default(true);
            $table->timestamps();
        });

        // Шаблоны писем с переменными для подстановки
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->json('variables_json')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Массовые рассылки администратора по группам пользователей
        Schema::create('bulk_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('target_group')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Получатели массовой рассылки с отметкой о доставке
        Schema::create('bulk_message_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('delivered_at')->nullable();
            $table->unique(['bulk_message_id', 'user_id'], 'uq_bulk_recipient');
        });
    }

    /**
     * Удаляет таблицы уведомлений и рассылок.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_message_recipients');
        Schema::dropIfExists('bulk_messages');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notifications');
    }
};
