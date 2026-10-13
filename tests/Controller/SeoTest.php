<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\InertiaWebTestCase;

final class SeoTest extends InertiaWebTestCase
{
    public function testPublicPageIsIndexable(): void
    {
        $this->visit('GET', '/');

        $this->page()
            ->where('seo.title', 'Home · Symfony Inertia Starter')
            ->has('seo.description')
            ->where('seo.canonical', 'http://localhost/')
            ->where('seo.image', 'http://localhost/og-image.png')
            ->where('seo.noindex', false);
    }

    public function testAuthAdminAndErrorPagesAreNotIndexed(): void
    {
        $this->visit('GET', '/login');
        $this->page()
            ->where('seo.title', 'Sign in · Symfony Inertia Starter')
            ->where('seo.canonical', null)
            ->where('seo.noindex', true);

        $this->visit('GET', '/does-not-exist');
        $this->page()
            ->where('seo.title', 'Not Found · Symfony Inertia Starter')
            ->where('seo.noindex', true);

        $this->loginAs('admin@example.com');
        $this->visit('GET', '/admin/technos');
        $this->page()
            ->where('seo.title', 'Technos · Symfony Inertia Starter')
            ->where('seo.noindex', true);
    }

    /**
     * Without SSR, Twig writes the head tags itself (templates/_seo.html.twig).
     * With SSR, see tests/ssr-smoke-test.sh.
     */
    public function testHeadTagsAreInTheHtmlEvenWithoutSsr(): void
    {
        if (!is_file(static::getContainer()->getParameter('kernel.project_dir').'/public/build/.vite/entrypoints.json')) {
            self::markTestSkipped('The HTML page needs the Vite entrypoints: run "npm run build" (or "make dev") first.');
        }

        // A plain browser request, without the X-Inertia header: the full HTML page
        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('html[lang="en"]');
        self::assertSelectorCount(1, 'title');
        self::assertSelectorTextSame('title', 'Home · Symfony Inertia Starter');
        self::assertSelectorExists('meta[name="description"][content]');
        self::assertSelectorExists('link[rel="canonical"][href="http://localhost/"]');
        self::assertSelectorExists('meta[property="og:image"][content="http://localhost/og-image.png"]');
        self::assertSelectorNotExists('meta[name="robots"]');
    }

    public function testSitemapListsThePublicPages(): void
    {
        $this->client->request('GET', '/sitemap.xml');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/xml; charset=UTF-8');

        $xml = simplexml_load_string((string) $this->client->getResponse()->getContent());
        self::assertNotFalse($xml);
        $locations = array_map(static fn (\SimpleXMLElement $url): string => (string) $url->loc, iterator_to_array($xml->url, false));
        self::assertSame(['http://localhost/', 'http://localhost/about'], $locations);
    }

    public function testRobotsTxtPointsToTheSitemap(): void
    {
        $this->client->request('GET', '/robots.txt');

        self::assertResponseIsSuccessful();
        $robots = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Disallow: /admin', $robots);
        self::assertStringContainsString('Sitemap: http://localhost/sitemap.xml', $robots);
    }
}
