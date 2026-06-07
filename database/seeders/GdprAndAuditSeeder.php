<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;

use App\Models\GdprConsent;
use App\Models\AuditEvent;
use App\Models\User;

class GdprAndAuditSeeder extends Seeder
{
    /**
     * Аудит
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        GdprConsent::truncate();
        AuditEvent::truncate();
        Schema::enableForeignKeyConstraints();

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        $users = User::all();
        foreach ($users as $user) {
            GdprConsent::factory()->create([
                'user_id'       => $user->id,
                'consent_type'  => 'privacy_policy',
                'version'       => '1.0',
            ]);
            GdprConsent::factory()->create([
                'user_id'       => $user->id,
                'consent_type'  => 'terms_of_service',
                'version'       => '2.0',
            ]);
        }
        AuditEvent::factory()->count(40)->create();
    }
}
