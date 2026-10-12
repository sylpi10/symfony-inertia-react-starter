<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use App\Tests\InertiaWebTestCase;

final class CsrfTest extends InertiaWebTestCase
{
    public function testPostWithoutTokenIsRejected(): void
    {
        $this->visit('GET', '/register');
        $this->visit('POST', '/register', [
            'email' => 'new@example.com',
            'password' => 'password1',
            'passwordConfirmation' => 'password1',
        ], csrf: false);

        // Sent back where we came from, with an error flash
        self::assertResponseRedirects('http://localhost/register', 303);
        $this->client->followRedirect();
        $this->page()->component('Auth/Register');
        self::assertSame('This page has expired. Please try again.', $this->flash('error'));

        // And nothing was written
        self::assertNull(static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'new@example.com']));
    }
}
