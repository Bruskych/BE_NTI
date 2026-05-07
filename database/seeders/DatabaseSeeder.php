<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Roly a oprávnenia — musí byť prvé
            RoleAndPermissionSeeder::class,

            // 2. Programy A a B
            ProgramSeeder::class,

            // 3. Špecializácie / tematické kategórie
            SpecializationSeeder::class,

            // 4. Šablóny hodnotenia + kritériá
            EvaluationTemplateSeeder::class,

            // 5. Testovacie používateľské účty + organizácia
            UserSeeder::class,

            // 6. Formulárové polia pre Program A a B
            FormFieldSeeder::class,

            // 7. Testovacia výzva pre Program A
            CallSeeder::class,

            // 8. Testovacie zadania pre Program B
            ChallengeSeeder::class,

            // 9. Testovacie tímy
            TeamSeeder::class,
        ]);
    }
}
