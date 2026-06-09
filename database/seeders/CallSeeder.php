<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

use App\Models\Call;
use App\Models\Program;
use App\Models\Specialization;
use App\Models\EvaluationTemplate;

/**
 * Сидер конкурсных вызовов (Calls): создаёт открытые периоды для приёма заявок Программы А.
 * Зависит от ProgramSeeder, SpecializationSeeder, EvaluationTemplateSeeder.
 */
class CallSeeder extends Seeder
{
    /**
     * Конкретный временной интервал, в который открывается окно для сбора заявок под программу A | B
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Call::truncate();
        DB::table('call_specialization')->truncate();
        Schema::enableForeignKeyConstraints();

        // ------------------------------
        // Ручное создание
        // ------------------------------

        if (Program::count() === 0) {
            $this->call(ProgramSeeder::class);
        }

        $specializations = Specialization::all();
        if ($specializations->isEmpty()) {
            $specializations = collect([
                Specialization::factory()->create(['name' => 'Frontend', 'slug' => 'frontend']),
                Specialization::factory()->create(['name' => 'Backend', 'slug' => 'backend'])
            ]);
        }

        $firstProgram = Program::first();
        $templateForFirst = EvaluationTemplate::where('program_id', $firstProgram->id)->first()
            ?? EvaluationTemplate::factory()->create(['program_id' => $firstProgram->id]);

        $staticCall = Call::create([
            'program_id'             => $firstProgram->id,
            'title'                  => 'Official NTI Spring Call 2026',
            'description'            => 'Static primary call for student software development grants.',
            'deadline'               => now()->addMonths(2),
            'status'                 => 'open',
            'budget'                 => '500000.00',
            'evaluation_template_id' => $templateForFirst->id,
        ]);
        $staticCall->specializations()->attach(
            $specializations->pluck('id')->toArray()
        );

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        for ($i = 0; $i < 4; $i++) {
            $randomProgram = Program::inRandomOrder()->first();

            $template = EvaluationTemplate::where('program_id', $randomProgram->id)->first()
                ?? EvaluationTemplate::factory()->create(['program_id' => $randomProgram->id]);

            $call = Call::factory()->create([
                'program_id'             => $randomProgram->id,
                'evaluation_template_id' => $template->id,
            ]);

            $call->specializations()->attach(
                $specializations->random(rand(1, $specializations->count()))->pluck('id')->toArray()
            );
        }
    }
}
