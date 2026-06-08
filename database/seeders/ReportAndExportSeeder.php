<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;

use App\Models\ExportsLog;

class ReportAndExportSeeder extends Seeder
{
    /**
     * Создание логов экспорта
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        ExportsLog::truncate();
        Schema::enableForeignKeyConstraints();

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        ExportsLog::factory()->count(30)->create();
    }
}
