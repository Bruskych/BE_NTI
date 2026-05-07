<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        Program::firstOrCreate(
            ['type' => 'grant'],
            [
                'name'        => 'Program A — Grantový inkubačný program',
                'description' => 'Grantový inkubačný program zameraný na vlastný inovatívny nápad uchádzača alebo tímu. Výsledkom má byť startup alebo produkt.',
                'is_active'   => true,
            ]
        );

        Program::firstOrCreate(
            ['type' => 'practice'],
            [
                'name'        => 'Program B — Živá prax',
                'description' => 'Program živej praxe prepájajúci reálne zadania zo súkromného sektora so študentskými tímami. NTI vystupuje ako sprostredkovateľ medzi firmou a realizačným tímom.',
                'is_active'   => true,
            ]
        );
    }
}
