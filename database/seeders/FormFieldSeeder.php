<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;

use App\Models\FormField;
use App\Models\Program;
use App\Models\Call;

/**
 * Сидер полей анкеты: создаёт динамические поля форм для подачи заявок.
 * Зависит от ProgramSeeder и CallSeeder.
 */
class FormFieldSeeder extends Seeder
{
    /**
     * Анкеты для подачи заявок
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        FormField::truncate();
        Schema::enableForeignKeyConstraints();

        // ------------------------------
        // Ручное создание
        // ------------------------------

        $program = Program::inRandomOrder()->first();
        $call = Call::first();

        if ($program) {
            FormField::create([
                'program_id'        => $program->id,
                'call_id'           => null,
                'name'              => 'motivation_letter',
                'label'             => 'Motivation Letter',
                'type'              => 'textarea',
                'required'          => true,
                'options_json'      => null,
                'validation_rules'  => 'required|string|min:100',
                'order'             => 1,
            ]);
            FormField::create([
                'program_id'        => $program->id,
                'call_id'           => null,
                'name'              => 'experience_level',
                'label'             => 'Your Technical Level',
                'type'              => 'select',
                'required'          => true,
                'options_json'      => ['Beginner', 'Intermediate', 'Advanced'],
                'validation_rules'  => 'required|string',
                'order'             => 2,
            ]);
        }

        if ($program && $call) {
            FormField::create([
                'program_id'        => $program->id,
                'call_id'           => $call->id,
                'name'              => 'github_repository',
                'label'             => 'Project GitHub Link',
                'type'              => 'text',
                'required'          => true,
                'options_json'      => null,
                'validation_rules'  => 'required|url',
                'order'             => 3,
            ]);
        }

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        FormField::factory()->count(10)->create();
    }
}
