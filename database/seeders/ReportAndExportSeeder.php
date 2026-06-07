<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;

use App\Models\ExportsLog;
use App\Models\Report;

class ReportAndExportSeeder extends Seeder
{
    /**
     * Создание отчетов и экспорта
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Report::truncate();
        ExportsLog::truncate();
        Schema::enableForeignKeyConstraints();

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        Report::factory()->count(15)->create();
        ExportsLog::factory()->count(30)->create();
    }
}
