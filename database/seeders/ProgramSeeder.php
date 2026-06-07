<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Program;

class ProgramSeeder extends Seeder
{
    /**
     * Программы А + Б
     */
    public function run(): void
    {
        // ------------------------------
        // Ручное создание
        // ------------------------------

        Program::firstOrCreate(
            ['type' => 'grant'],
            [
                'name'          => 'Program A — Grant Incubation Program',
                'description'   => 'A grant incubation program focused on the applicants or teams own innovative idea. The result should be a startup or product.',
                'is_active'     => true,
            ]
        );
        Program::firstOrCreate(
            ['type' => 'practice'],
            [
                'name'          => 'Program B — Live practice',
                'description'   => 'A live practice program connecting real-world assignments from the private sector with student teams. NTI acts as an intermediary between the company and the implementation team.',
                'is_active'     => true,
            ]
        );
    }
}
