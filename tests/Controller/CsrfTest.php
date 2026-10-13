<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use App\Tests\InertiaWebTestCase;

final class CsrfTest extends InertiaWebTestCase
{
    public function testCrossSitePostIsRejected(): void
    {
        $this->visit('GET', '/register');
        $this->visit('POST', '/register', [
            'email' => 'new@example.com',
            'password' => 'password1',
            'passwordConfirmation' => 'password1',
        ], crossSite: true);

        // Sent back where we came from, with an error flash
        self::assertResponseRedirects('http://localhost/register', 303);
        $this->client->followRedirect();
        $this->page()->component('Auth/Register');
        self::assertSame('This page has expired. Please try again.', $this->flash('error'));

        // And nothing was written
        self::assertNull(static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'new@example.com']));
    }

    public function testPostWithoutAnyOriginInformationIsRejected(): void
    {
        // Neither Sec-Fetch-Site, nor Origin, nor Referer: not a browser the app can trust
        $this->client->request('POST', '/register', server: ['HTTP_X_INERTIA' => 'true', 'CONTENT_TYPE' => 'application/json'], content: '{}');

        self::assertResponseRedirects('/', 303);
    }

    public function testAnonymousVisitorsGetNoSessionAndNoCookie(): void
    {
        foreach (['/', '/about', '/login', '/sitemap.xml', '/robots.txt', '/does-not-exist'] as $uri) {
            $this->visit('GET', $uri);

            self::assertSame([], $this->client->getResponse()->headers->getCookies(), sprintf('"%s" sets a cookie.', $uri));
        }

        self::assertCount(0, $this->client->getCookieJar()->all());
    }
}
