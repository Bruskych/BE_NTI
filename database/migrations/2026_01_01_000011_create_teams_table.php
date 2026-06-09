<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблицы студенческих команд: teams, team_specialization, team_user.
 * Команда является основной единицей, подающей заявки на вызовы и челленджи.
 */
return new class extends Migration
{
    /**
     * Создаёт таблицу teams и сводные таблицы участников и специализаций команды.
     */
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->foreignId('leader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->json('skills_json')->nullable();
            $table->integer('capacity')->nullable();
            $table->string('status', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Специализации команды (многие-ко-многим)
        Schema::create('team_specialization', function (Blueprint $table) {
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialization_id')->constrained()->cascadeOnDelete();
            $table->primary(['team_id', 'specialization_id']);
        });

        // Участники команды с ролью (leader или member) и датой вступления
        Schema::create('team_user', function (Blueprint $table) {
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['leader', 'member'])->default('member');
            $table->timestamp('joined_at')->nullable();
            $table->primary(['team_id', 'user_id']);
        });
    }

    /**
     * Удаляет таблицы team_user, team_specialization и teams.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('team_specialization');
        Schema::dropIfExists('teams');
    }
};
