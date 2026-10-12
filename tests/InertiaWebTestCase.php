<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Nytodev\InertiaBundle\Testing\AssertableInertiaPage;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
     * Sends an Inertia request: X-Inertia header, plus the XSRF-TOKEN cookie
     * copied into the X-XSRF-TOKEN header, as the Inertia client does.
     *
     * @param array<string, mixed> $data
     */
    protected function visit(string $method, string $uri, array $data = [], bool $csrf = true, bool $asForm = false): void
    {
        $server = ['HTTP_X_INERTIA' => 'true'];

        $token = $this->client->getCookieJar()->get('XSRF-TOKEN');
        if ($csrf && null !== $token) {
            $server['HTTP_X_XSRF_TOKEN'] = $token->getValue();
        }

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

    protected function loginAs(string $email): void
    {
        $user = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => $email]);
        self::assertInstanceOf(User::class, $user, sprintf('User "%s" not found: are the fixtures loaded?', $email));

        $this->client->loginUser($user);
    }
}
