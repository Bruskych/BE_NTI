<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use App\Models\Page;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * Статические страницы
     *
     * @return array<string, mixed>
     */
    protected $model = Page::class;
    public function definition(): array
    {
        $title = fake()->sentence(3);
        return [
            'title'             => $title,
            'slug'              => Str::slug($title),
            'content'           => fake()->paragraphs(6, true),
            'meta_title'        => $title,
            'meta_description'  => fake()->sentence(12),
            'is_published'      => fake()->boolean(90),
        ];
    }
}
