<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблицы организаций (компаний) и их членов.
 * Организации выступают заказчиками в Программе Б (челленджи).
 */
return new class extends Migration
{
    /**
     * Создаёт таблицы organizations и сводную organization_user.
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tax_id', 100)->nullable();
            $table->string('sector')->nullable();
            $table->string('website_link', 500)->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // Сводная таблица: роли пользователей внутри организации
        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['owner', 'member', 'product_owner'])->default('member');
            $table->timestamps();
        });
    }

    /**
     * Удаляет таблицы organization_user и organizations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_user');
        Schema::dropIfExists('organizations');
    }
};
