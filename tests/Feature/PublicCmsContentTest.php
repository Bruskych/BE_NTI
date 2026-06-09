<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Spec 6.1: "Verejný web a CMS vrstva" — the public website (home page,
 * program landing pages, news, FAQ, ...) must be reachable by guests,
 * not only authenticated users.
 */
class PublicCmsContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_and_view_published_pages()
    {
        $published = Page::factory()->create(['is_published' => true]);
        Page::factory()->create(['is_published' => false]);

        $this->getJson('/api/pages')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $published->id);

        $this->getJson("/api/pages/{$published->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $published->id);
    }

    public function test_guest_cannot_view_an_unpublished_page()
    {
        $draft = Page::factory()->create(['is_published' => false]);

        $this->getJson("/api/pages/{$draft->id}")
            ->assertStatus(403);
    }

    public function test_guest_can_list_and_view_published_posts()
    {
        $published = Post::factory()->create(['is_published' => true]);
        Post::factory()->create(['is_published' => false]);

        $this->getJson('/api/posts')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $published->id);

        $this->getJson("/api/posts/{$published->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $published->id);
    }

    public function test_guest_cannot_view_an_unpublished_post()
    {
        $draft = Post::factory()->create(['is_published' => false]);

        $this->getJson("/api/posts/{$draft->id}")
            ->assertStatus(403);
    }

    public function test_sitemap_lists_only_published_pages_and_posts()
    {
        $publishedPage = Page::factory()->create(['is_published' => true, 'slug' => 'about-us']);
        Page::factory()->create(['is_published' => false, 'slug' => 'draft-page']);

        $publishedPost = Post::factory()->create(['is_published' => true, 'slug' => 'launch-news']);
        Post::factory()->create(['is_published' => false, 'slug' => 'draft-post']);

        $response = $this->get('/api/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');

        $xml = simplexml_load_string($response->getContent());
        $locations = collect();
        foreach ($xml->url as $urlNode) {
            $locations->push((string) $urlNode->loc);
        }

        $baseUrl = rtrim(config('app.frontend_url'), '/');

        $this->assertTrue($locations->contains($baseUrl . '/' . $publishedPage->slug));
        $this->assertTrue($locations->contains($baseUrl . '/blog/' . $publishedPost->slug));
        $this->assertFalse($locations->contains($baseUrl . '/draft-page'));
        $this->assertFalse($locations->contains($baseUrl . '/blog/draft-post'));
    }
}
