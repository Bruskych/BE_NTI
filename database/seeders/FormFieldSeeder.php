<?php

namespace Database\Seeders;

use App\Models\FormField;
use App\Models\Program;
use Illuminate\Database\Seeder;

class FormFieldSeeder extends Seeder
{
    public function run(): void
    {
        $programA = Program::where('type', 'grant')->first();
        $programB = Program::where('type', 'practice')->first();

        // ---------------------------------------------------------
        // Polia pre Program A (platné pre celý program)
        // ---------------------------------------------------------
        $fieldsA = [
            [
                'name'             => 'project_name',
                'label'            => 'Názov projektu',
                'type'             => 'text',
                'required'         => true,
                'validation_rules' => 'required|string|max:255',
                'order'            => 1,
            ],
            [
                'name'             => 'project_description',
                'label'            => 'Popis projektu',
                'type'             => 'textarea',
                'required'         => true,
                'validation_rules' => 'required|string|max:2000',
                'order'            => 2,
            ],
            [
                'name'             => 'problem_statement',
                'label'            => 'Aký problém projekt rieši?',
                'type'             => 'textarea',
                'required'         => true,
                'validation_rules' => 'required|string|max:1000',
                'order'            => 3,
            ],
            [
                'name'             => 'target_group',
                'label'            => 'Cieľová skupina',
                'type'             => 'textarea',
                'required'         => true,
                'validation_rules' => 'required|string|max:500',
                'order'            => 4,
            ],
            [
                'name'             => 'innovation_description',
                'label'            => 'Čím je projekt inovatívny?',
                'type'             => 'textarea',
                'required'         => true,
                'validation_rules' => 'required|string|max:1000',
                'order'            => 5,
            ],
            [
                'name'             => 'budget_breakdown',
                'label'            => 'Rozpočet projektu',
                'type'             => 'textarea',
                'required'         => true,
                'validation_rules' => 'required|string|max:1000',
                'order'            => 6,
            ],
            [
                'name'             => 'timeline',
                'label'            => 'Časový harmonogram',
                'type'             => 'textarea',
                'required'         => true,
                'validation_rules' => 'required|string|max:1000',
                'order'            => 7,
            ],
            [
                'name'             => 'pitch_deck',
                'label'            => 'Pitch deck (PDF)',
                'type'             => 'file',
                'required'         => false,
                'validation_rules' => 'nullable|file|mimes:pdf|max:10240',
                'order'            => 8,
            ],
        ];

        foreach ($fieldsA as $field) {
            FormField::firstOrCreate(
                ['program_id' => $programA->id, 'call_id' => null, 'name' => $field['name']],
                $field + ['program_id' => $programA->id, 'call_id' => null]
            );
        }

        // ---------------------------------------------------------
        // Polia pre Program B (platné pre celý program)
        // ---------------------------------------------------------
        $fieldsB = [
            [
                'name'             => 'team_introduction',
                'label'            => 'Predstavenie tímu',
                'type'             => 'textarea',
                'required'         => true,
                'validation_rules' => 'required|string|max:1000',
                'order'            => 1,
            ],
            [
                'name'             => 'relevant_experience',
                'label'            => 'Relevantné skúsenosti tímu',
                'type'             => 'textarea',
                'required'         => true,
                'validation_rules' => 'required|string|max:1000',
                'order'            => 2,
            ],
            [
                'name'             => 'solution_approach',
                'label'            => 'Navrhovaný prístup k riešeniu',
                'type'             => 'textarea',
                'required'         => true,
                'validation_rules' => 'required|string|max:2000',
                'order'            => 3,
            ],
            [
                'name'             => 'motivation',
                'label'            => 'Motivácia a záujem o zadanie',
                'type'             => 'textarea',
                'required'         => true,
                'validation_rules' => 'required|string|max:1000',
                'order'            => 4,
            ],
            [
                'name'             => 'availability',
                'label'            => 'Časová dostupnosť tímu (hod/týždeň)',
                'type'             => 'number',
                'required'         => true,
                'validation_rules' => 'required|integer|min:1|max:40',
                'order'            => 5,
            ],
        ];

        foreach ($fieldsB as $field) {
            FormField::firstOrCreate(
                ['program_id' => $programB->id, 'call_id' => null, 'name' => $field['name']],
                $field + ['program_id' => $programB->id, 'call_id' => null]
            );
        }
    }
}
