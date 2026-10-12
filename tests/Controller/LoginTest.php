<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\InertiaWebTestCase;

final class LoginTest extends InertiaWebTestCase
{
    public function testLoginIgnoresTheCaseOfTheEmail(): void
    {
        $this->login('Test@Example.COM', 'test1234');

        self::assertResponseRedirects('http://localhost/');
        $this->client->followRedirect();
        $this->page()->component('Home')->where('auth.user.email', 'test@example.com');
    }

    public function testWrongPasswordShowsAnError(): void
    {
        $this->login('test@example.com', 'wrong');

        self::assertResponseRedirects('http://localhost/login');
        $this->client->followRedirect();
        $this->page()
            ->component('Auth/Login')
            ->where('errors.email', 'Invalid credentials.')
            ->where('lastEmail', 'test@example.com')
            ->where('auth.user', null);
    }

    public function testLoginIsThrottledAfterFiveFailures(): void
    {
        for ($attempt = 1; $attempt <= 5; ++$attempt) {
            $this->login('test@example.com', 'wrong');
        }

        // Even the right password is refused now
        $this->login('test@example.com', 'test1234');

        self::assertResponseRedirects('http://localhost/login');
        $this->client->followRedirect();
        $this->page()
            ->where('errors.email', 'Too many failed login attempts, please try again in 15 minutes.')
            ->where('auth.user', null);
    }

    private function login(string $email, string $password): void
    {
        $this->visit('GET', '/login');
        $this->visit('POST', '/login', ['email' => $email, 'password' => $password], asForm: true);
    }
}
