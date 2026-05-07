<?php

namespace Database\Seeders;

use App\Models\Call;
use App\Models\EvaluationTemplate;
use App\Models\Program;
use App\Models\Specialization;
use Illuminate\Database\Seeder;

class CallSeeder extends Seeder
{
    public function run(): void
    {
        $programA  = Program::where('type', 'grant')->first();
        $template  = EvaluationTemplate::where('program_id', $programA->id)->first();

        $call = Call::firstOrCreate(
            ['title' => 'Výzva č. 1 / 2026 — Grantový inkubačný program'],
            [
                'program_id'             => $programA->id,
                'description'            => 'Prvá výzva grantového inkubačného programu NTI pre akademický rok 2025/2026. Podporujeme inovatívne projekty študentov v oblasti softvérových technológií.',
                'deadline'               => now()->addMonths(2),
                'status'                 => 'open',
                'budget'                 => 50000.00,
                'evaluation_template_id' => $template?->id,
            ]
        );

        // Priraď špecializácie
        $specializations = Specialization::whereIn('slug', [
            'vyvoj-softveru',
            'ai-datove-technologie',
            'webove-aplikacie',
        ])->pluck('id');

        $call->specializations()->syncWithoutDetaching($specializations);
    }
}
