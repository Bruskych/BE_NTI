<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Добавляет поддержку мягкого удаления (soft deletes) в таблицу evaluation_scores.
 */
return new class extends Migration
{
    /**
     * Добавляет столбец deleted_at в таблицу evaluation_scores (если он ещё не существует).
     */
    public function up(): void
    {
        Schema::table('evaluation_scores', function (Blueprint $table) {
            if (!Schema::hasColumn('evaluation_scores', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Удаляет столбец deleted_at из таблицы evaluation_scores.
     */
    public function down(): void
    {
        Schema::table('evaluation_scores', function (Blueprint $table) {
            if (Schema::hasColumn('evaluation_scores', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
