<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

use App\Models\Partner;
use App\Models\Post;
use App\Models\Page;

/**
 * Сидер CMS-контента: создаёт статические страницы, посты блога и партнёров платформы.
 * Не зависит от других сидеров — может выполняться на любом этапе.
 */
class CmsAndPartnerSeeder extends Seeder
{
    /**
     * Партнёры и контент
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Post::truncate();
        Page::truncate();
        Partner::truncate();
        Schema::enableForeignKeyConstraints();

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        $defaultPages = [
            'About NTI',
            'Contacts',
            'Privacy Policy',
            'Terms of Use'
        ];

        foreach ($defaultPages as $pageTitle) {
            Page::factory()->create([
                'title' => $pageTitle,
                'slug'  => Str::slug($pageTitle),
                'is_published' => true,
            ]);
        }

        Page::factory()->count(2)->create();
        Post::factory()->count(15)->create();
        Partner::factory()->count(8)->create();
    }
}
