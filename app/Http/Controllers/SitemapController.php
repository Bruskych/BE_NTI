<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\Response;
use SimpleXMLElement;

/**
 * Spec 6.1: "správa meta-title, meta-description, OG obrázkov, URL slugov a sitemap.xml"
 * — generates the sitemap for the public website from published CMS content.
 */
/** Контроллер генерации sitemap.xml из опубликованных страниц и постов */
class SitemapController extends Controller
{
    /** Генерирует XML-карту сайта из опубликованных страниц и постов */
    public function index(): Response
    {
        $baseUrl = rtrim(config('app.frontend_url'), '/');

        $entries = collect([['loc' => $baseUrl . '/', 'lastmod' => null]])
            ->merge($this->entriesFor(Page::where('is_published', true)->get(['slug', 'updated_at']), $baseUrl . '/%s'))
            ->merge($this->entriesFor(Post::where('is_published', true)->get(['slug', 'updated_at']), $baseUrl . '/blog/%s'));

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

        foreach ($entries as $entry) {
            $url = $xml->addChild('url');
            $url->addChild('loc', $this->escape($entry['loc']));

            if ($entry['lastmod']) {
                $url->addChild('lastmod', $entry['lastmod']->toAtomString());
            }
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    /** Преобразует коллекцию моделей в массив записей для sitemap */
    private function entriesFor($models, string $urlTemplate)
    {
        return $models
            ->filter(fn ($model) => filled($model->slug))
            ->map(fn ($model) => [
                'loc' => sprintf($urlTemplate, ltrim($model->slug, '/')),
                'lastmod' => $model->updated_at,
            ]);
    }

    /** Экранирует строку для безопасного включения в XML */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
