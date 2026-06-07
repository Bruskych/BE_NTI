<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Specialization;

class SpecializationSeeder extends Seeder
{
    /**
     * Специализации / Направления
     */
    public function run(): void
    {
        // ------------------------------
        // Ручное создание
        // ------------------------------

        $specializations = [
            [
                'name'        => 'Software Development',
                'slug'        => 'software-development',
                'description' => 'Desktop, mobile applications, embedded systems.',
            ],
            [
                'name'        => 'AI and Data Technologies',
                'slug'        => 'ai-data-technologies',
                'description' => 'AI applications, data technologies, machine learning.',
            ],
            [
                'name'        => 'Web Applications',
                'slug'        => 'web-applications',
                'description' => 'Internet and browser applications.',
            ],
            [
                'name'        => 'Game Development',
                'slug'        => 'game-development',
                'description' => 'Game applications, language and platform.',
            ],
            [
                'name'        => 'IoT and Embedded Systems',
                'slug'        => 'iot-embedded-systems',
                'description' => 'Software and hardware components of the Internet of Things.',
            ],
            [
                'name'        => 'Qualification Stack 01 — Object Technologies',
                'slug'        => 'stack-01-object-technologies',
                'description' => 'Object technologies, mobile applications, testing.',
            ],
            [
                'name'        => 'Qualification Stack 02 — AI and Data',
                'slug'        => 'stack-02-ai-data',
                'description' => 'Database systems, AI, machine learning, neural networks.',
            ],
            [
                'name'        => 'Qualification Stack 03 — Web',
                'slug'        => 'stack-03-web',
                'description' => 'Web languages, FE/BE technologies, web applications.',
            ],
            [
                'name'        => 'Qualification Stack 04 — Game Development and VR',
                'slug'        => 'stack-04-game-development-vr',
                'description' => 'Game development environments, VR and augmented reality.',
            ],
            [
                'name'        => 'Qualification Stack 05 — IoT and Robotics',
                'slug'        => 'stack-05-iot-robotics',
                'description' => 'C programming, Internet of Things, robotic systems.',
            ],
        ];
        foreach ($specializations as $item) {
            Specialization::firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'name'        => $item['name'],
                    'description' => $item['description'],
                ]
            );
        }
    }
}
