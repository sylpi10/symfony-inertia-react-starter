<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Nytodev\InertiaBundle\Testing\AssertableInertiaPage;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Email;

/**
 * Makes a WebTestCase talk to the app the way the Inertia client does.
 */
abstract class InertiaWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        // Login throttling counters live in a cache pool, not in the database:
        // without this, failed logins would add up from one test (and one run) to the next.
        static::getContainer()->get('cache.rate_limiter')->clear();
    }

    /**
     * Sends an Inertia request the way a browser does: X-Inertia header, plus
     * Sec-Fetch-Site, the header CSRF protection relies on. Pass crossSite: true
     * to play a request forged by another site.
     *
     * @param array<string, mixed> $data
     */
    protected function visit(string $method, string $uri, array $data = [], bool $crossSite = false, bool $asForm = false): void
    {
        $server = [
            'HTTP_X_INERTIA' => 'true',
            'HTTP_SEC_FETCH_SITE' => $crossSite ? 'cross-site' : 'same-origin',
        ];

        // The login form is posted as form data (forceFormData), everything else as JSON
        if ($asForm) {
            $this->client->request($method, $uri, $data, server: $server);

            return;
        }

        $this->client->request(
            $method,
            $uri,
            server: $server + ['CONTENT_TYPE' => 'application/json'],
            content: [] === $data ? null : json_encode($data, \JSON_THROW_ON_ERROR),
        );
    }

    protected function page(): AssertableInertiaPage
    {
        return AssertableInertiaPage::fromResponse($this->client->getResponse());
    }

    /**
     * Flash messages are not props: they sit next to them in the page object.
     */
    protected function flash(string $key): ?string
    {
        $page = json_decode((string) $this->client->getResponse()->getContent(), true, flags: \JSON_THROW_ON_ERROR);

        return $page['flash'][$key] ?? null;
    }

    /**
     * Returns the first link matching $pattern in the email sent by the last request.
     * Must be called right after that request.
     */
    protected function linkFromEmail(string $pattern): string
    {
        $email = self::getMailerMessage();
        self::assertInstanceOf(Email::class, $email, 'No email was sent by the last request.');

        self::assertSame(
            1,
            preg_match('#href="([^"]*'.$pattern.'[^"]*)"#', (string) $email->getHtmlBody(), $matches),
            sprintf('No link matching "%s" in the email.', $pattern),
        );

        return html_entity_decode($matches[1]);
    }

    protected function loginAs(string $email): void
    {
        $user = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => $email]);
        self::assertInstanceOf(User::class, $user, sprintf('User "%s" not found: are the fixtures loaded?', $email));

        $this->client->loginUser($user);
    }
}
