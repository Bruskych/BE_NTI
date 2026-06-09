<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Добавляет поддержку мягкого удаления (soft deletes) в таблицу exports_log.
 */
return new class extends Migration
{
    /**
     * Добавляет столбец deleted_at в таблицу exports_log (если он ещё не существует).
     */
    public function up(): void
    {
        Schema::table('exports_log', function (Blueprint $table) {
            if (!Schema::hasColumn('exports_log', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Удаляет столбец deleted_at из таблицы exports_log.
     */
    public function down(): void
    {
        Schema::table('exports_log', function (Blueprint $table) {
            if (Schema::hasColumn('exports_log', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
