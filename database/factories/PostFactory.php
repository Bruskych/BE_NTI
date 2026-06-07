<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use App\Models\Post;
use App\Models\User;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Посты / Блог / Новости
     *
     * @return array<string, mixed>
     */
    protected $model = Post::class;
    public function definition(): array
    {
        $title = fake()->sentence(5);
        $isPublished = fake()->boolean(80);
        return [
            'author_id'         => fn() => User::whereHas('roles', fn($q) => $q->whereIn('name', ['content_editor', 'admin', 'super_admin']))->inRandomOrder()->first()?->id ?? User::factory(),
            'title'             => $title,
            'slug'              => Str::slug($title),
            'excerpt'           => fake()->paragraph(2),
            'content'           => fake()->paragraphs(5, true),
            'featured_image'    => 'cms/posts/' . fake()->uuid() . '.jpg',
            'meta_title'        => $title,
            'meta_description'  => fake()->sentence(10),
            'og_image'          => 'cms/posts/' . fake()->uuid() . '_og.jpg',
            'is_published'      => $isPublished,
            'published_at'      => $isPublished ? fake()->dateTimeBetween('-4 months', 'now') : null,
        ];
    }
}
