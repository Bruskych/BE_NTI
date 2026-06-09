<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('specializations', function (Blueprint $table) {
            // Квалификационный стек Программы А (01–05); null — тематическая категория
            $table->string('stack', 2)->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('specializations', function (Blueprint $table) {
            $table->dropColumn('stack');
        });
    }
};
