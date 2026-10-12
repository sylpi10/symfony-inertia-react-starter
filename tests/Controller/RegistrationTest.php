<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\InertiaWebTestCase;

final class RegistrationTest extends InertiaWebTestCase
{
    public function testInvalidDataComesBackAsErrors(): void
    {
        $this->visit('GET', '/register');
        $this->visit('POST', '/register', [
            'email' => 'TEST@example.com', // already taken, whatever the case
            'password' => 'short',
            'passwordConfirmation' => 'different',
        ]);

        self::assertResponseRedirects('http://localhost/register', 303);
        $this->client->followRedirect();
        $this->page()
            ->component('Auth/Register')
            ->where('errors.email', 'An account already exists with this email.')
            ->has('errors.password')
            ->where('errors.passwordConfirmation', 'The passwords do not match.');
    }

    public function testRegistrationLogsInAndSendsTheConfirmationEmail(): void
    {
        $this->visit('GET', '/register');
        $this->visit('POST', '/register', [
            'email' => 'New@Example.com',
            'password' => 'password1',
            'passwordConfirmation' => 'password1',
        ]);

        self::assertResponseRedirects('/', 303);
        self::assertEmailCount(1);

        $this->client->followRedirect();
        $this->page()
            ->component('Home')
            ->where('auth.user.email', 'new@example.com')
            ->where('auth.user.isVerified', false);
        self::assertSame('Welcome! Check your inbox to confirm your email address.', $this->flash('success'));
    }
}
