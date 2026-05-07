<?php

namespace Database\Seeders;

use App\Models\Specialization;
use Illuminate\Database\Seeder;

class SpecializationSeeder extends Seeder
{
    public function run(): void
    {
        $specializations = [
            ['name' => 'Vývoj softvéru',             'slug' => 'vyvoj-softveru',             'description' => 'Desktop, mobilné aplikácie, embedded systémy.'],
            ['name' => 'AI a dátové technológie',     'slug' => 'ai-datove-technologie',       'description' => 'AI aplikácie, dátové technológie, strojové učenie.'],
            ['name' => 'Webové aplikácie',            'slug' => 'webove-aplikacie',            'description' => 'Internetové a prehliadačové aplikácie.'],
            ['name' => 'Herný vývoj',                 'slug' => 'herny-vyvoj',                 'description' => 'Herné aplikácie, jazyk a platforma.'],
            ['name' => 'IoT a embedded systémy',      'slug' => 'iot-embedded-systemy',        'description' => 'Softvérové aj hardvérové komponenty internetu vecí.'],
            ['name' => 'Kvalifikačný stack 01 — Objektové technológie', 'slug' => 'stack-01-objektove-technologie', 'description' => 'Objektové technológie, mobilné aplikácie, testovanie.'],
            ['name' => 'Kvalifikačný stack 02 — AI a dáta',             'slug' => 'stack-02-ai-data',              'description' => 'Databázové systémy, AI, strojové učenie, neurónové siete.'],
            ['name' => 'Kvalifikačný stack 03 — Web',                   'slug' => 'stack-03-web',                  'description' => 'Jazyky webu, FE/BE technológie, webové aplikácie.'],
            ['name' => 'Kvalifikačný stack 04 — Herný vývoj a VR',      'slug' => 'stack-04-herny-vyvoj-vr',       'description' => 'Herné vývojové prostredia, VR a rozšírená realita.'],
            ['name' => 'Kvalifikačný stack 05 — IoT a robotika',        'slug' => 'stack-05-iot-robotika',         'description' => 'Programovanie v C, internet vecí, robotické systémy.'],
        ];

        foreach ($specializations as $data) {
            Specialization::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
