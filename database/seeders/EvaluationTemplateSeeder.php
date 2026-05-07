<?php

namespace Database\Seeders;

use App\Models\EvaluationCriteria;
use App\Models\EvaluationTemplate;
use App\Models\Program;
use Illuminate\Database\Seeder;

class EvaluationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $programA = Program::where('type', 'grant')->first();
        $programB = Program::where('type', 'practice')->first();

        // ---------------------------------------------------------
        // Šablóna pre Program A
        // ---------------------------------------------------------
        $templateA = EvaluationTemplate::firstOrCreate(
            ['name' => 'Štandardná šablóna hodnotenia — Program A'],
            [
                'program_id'  => $programA->id,
                'description' => 'Základná šablóna hodnotenia pre grantový inkubačný program.',
            ]
        );

        $criteriaA = [
            ['name' => 'Inovácia a originalita', 'description' => 'Miera inovatívnosti nápadu a jeho originalita na trhu.', 'weight' => 25.00, 'order' => 1],
            ['name' => 'Realizovateľnosť', 'description' => 'Technická a organizačná realizovateľnosť projektu.', 'weight' => 20.00, 'order' => 2],
            ['name' => 'Trhový potenciál', 'description' => 'Potenciál projektu na trhu a škálovateľnosť riešenia.', 'weight' => 20.00, 'order' => 3],
            ['name' => 'Tím a kompetencie', 'description' => 'Skúsenosti a kompetencie členov tímu.', 'weight' => 20.00, 'order' => 4],
            ['name' => 'Prezentácia a dokumentácia', 'description' => 'Kvalita prezentácie projektu a priložených dokumentov.', 'weight' => 15.00, 'order' => 5],
        ];

        foreach ($criteriaA as $criteria) {
            EvaluationCriteria::firstOrCreate(
                ['template_id' => $templateA->id, 'name' => $criteria['name']],
                $criteria
            );
        }

        // ---------------------------------------------------------
        // Šablóna pre Program B
        // ---------------------------------------------------------
        $templateB = EvaluationTemplate::firstOrCreate(
            ['name' => 'Štandardná šablóna hodnotenia — Program B'],
            [
                'program_id'  => $programB->id,
                'description' => 'Základná šablóna hodnotenia pre program živej praxe.',
            ]
        );

        $criteriaB = [
            ['name' => 'Technické kompetencie tímu', 'description' => 'Úroveň technických zručností tímu vo vzťahu k zadaniu.', 'weight' => 30.00, 'order' => 1],
            ['name' => 'Pochopenie zadania', 'description' => 'Miera pochopenia firemného zadania a jeho požiadaviek.', 'weight' => 25.00, 'order' => 2],
            ['name' => 'Návrh riešenia', 'description' => 'Kvalita a reálnosť navrhovaného riešenia.', 'weight' => 25.00, 'order' => 3],
            ['name' => 'Motivácia a záujem', 'description' => 'Motivácia tímu a záujem o danú problematiku.', 'weight' => 20.00, 'order' => 4],
        ];

        foreach ($criteriaB as $criteria) {
            EvaluationCriteria::firstOrCreate(
                ['template_id' => $templateB->id, 'name' => $criteria['name']],
                $criteria
            );
        }
    }
}
