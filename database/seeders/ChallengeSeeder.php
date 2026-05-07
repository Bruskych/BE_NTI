<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\Organization;
use App\Models\Program;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChallengeSeeder extends Seeder
{
    public function run(): void
    {
        $programB     = Program::where('type', 'practice')->first();
        $organization = Organization::where('tax_id', 'SK123456789')->first();
        $productOwner = User::where('email', 'company@firma.sk')->first();

        $challenge = Challenge::firstOrCreate(
            ['title' => 'Vývoj interného HR portálu'],
            [
                'program_id'              => $programB->id,
                'organization_id'         => $organization->id,
                'description'             => 'Firma hľadá tím pre vývoj interného HR portálu na správu zamestnancov, dovoleniek a hodnotení. Portál má byť webová aplikácia s moderným UX.',
                'technical_specification' => "## Technické požiadavky\n\n- Backend: Laravel 11 alebo Node.js\n- Frontend: Vue.js alebo React\n- Databáza: MySQL alebo PostgreSQL\n- Autentifikácia: SSO / OAuth2\n- REST API pre mobilnú aplikáciu\n\n## Funkcionality\n\n- Správa zamestnancov (CRUD)\n- Evidencia dovoleniek a absencií\n- Hodnotenie výkonu\n- Reporty a exporty\n- Notifikácie (email + push)",
                'budget'                  => 8000.00,
                'product_owner_id'        => $productOwner->id,
                'deadline'                => now()->addMonths(4),
                'status'                  => 'published',
                'max_applications'        => 3,
                'backlog_order'           => 1,
            ]
        );

        $specializations = Specialization::whereIn('slug', [
            'webove-aplikacie',
            'stack-03-web',
        ])->pluck('id');

        $challenge->specializations()->syncWithoutDetaching($specializations);

        // Druhé zadanie
        $challenge2 = Challenge::firstOrCreate(
            ['title' => 'AI chatbot pre zákaznícku podporu'],
            [
                'program_id'              => $programB->id,
                'organization_id'         => $organization->id,
                'description'             => 'Vývoj AI chatbota pre automatizáciu zákazníckej podpory s integráciou na existujúci helpdesk systém.',
                'technical_specification' => "## Technické požiadavky\n\n- LLM integrácia (OpenAI API alebo podobné)\n- Backend: Python (FastAPI) alebo Node.js\n- Integrácia so Zendesk / Freshdesk API\n- Tréning na vlastných dátach firmy\n\n## Funkcionality\n\n- Automatické odpovedanie na časté otázky\n- Eskalácia na ľudského agenta\n- Analýza sentimentu\n- Dashboard s metrikami",
                'budget'                  => 6000.00,
                'product_owner_id'        => $productOwner->id,
                'deadline'                => now()->addMonths(3),
                'status'                  => 'published',
                'max_applications'        => 3,
                'backlog_order'           => 2,
            ]
        );

        $specializations2 = Specialization::whereIn('slug', [
            'ai-datove-technologie',
            'stack-02-ai-data',
        ])->pluck('id');

        $challenge2->specializations()->syncWithoutDetaching($specializations2);
    }
}
