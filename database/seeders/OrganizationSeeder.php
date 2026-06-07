<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

use App\Models\Organization;
use App\Models\User;

class OrganizationSeeder extends Seeder
{
    /**
     * Создание компаний/фирм
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Organization::truncate();
        Schema::enableForeignKeyConstraints();

        // ------------------------------
        // Ручное создание
        // ------------------------------

        $companyUser = User::where('email', 'company@firma.sk')->first();
        if ($companyUser) {
            $organization = Organization::create(
                [
                    'name'         => 'TestFirma s.r.o.',
                    'sector'       => 'IT & Technology',
                    'website_link' => 'https://testfirma.sk',
                    'description'  => 'Testing company for NTI Program B.',
                    'status'       => 'active',
                    'tax_id'       => 'US123456789',
                ]
            );
            $organization->users()->syncWithoutDetaching([
                $companyUser->id => ['role' => 'owner'],
            ]);
        }

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        for ($i = 0; $i < 2; $i++) {
            $newCompanyUser = User::factory()->create();
            $newCompanyUser->assignRole('company');
            Organization::factory()->withOwner($newCompanyUser)->create();
        }
        for ($i = 0; $i < 2; $i++) {
            $newCompanyUser = User::factory()->create();
            $newCompanyUser->assignRole('company');
            Organization::factory()->withProductOwner($newCompanyUser)->create();
        }
        for ($i = 0; $i < 2; $i++) {
            $newCompanyUser = User::factory()->create();
            $newCompanyUser->assignRole('company');
            Organization::factory()->withMember($newCompanyUser)->create();
        }
        for ($i = 0; $i < 3; $i++) {
            $inactiveUser = User::factory()->create();
            $inactiveUser->assignRole('company');
            Organization::factory()->inactive()->withOwner($inactiveUser)->create();
        }
    }
}
