<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\InertiaWebTestCase;

final class ResetPasswordTest extends InertiaWebTestCase
{
    public function testFullResetFlow(): void
    {
        // Ask for a reset link
        $this->visit('GET', '/reset-password');
        $this->visit('POST', '/reset-password', ['email' => 'Test@Example.com']);

        self::assertResponseRedirects('/reset-password/check-email', 303);
        self::assertEmailCount(1);
        $link = $this->linkFromEmail('/reset-password/reset/');

        // Follow the link: the token moves to the session, then the form is shown
        $this->visit('GET', $link);
        self::assertResponseRedirects('/reset-password/reset');
        $this->client->followRedirect();
        $this->page()->component('Auth/ResetPassword');

        // Choose a new password
        $this->visit('POST', '/reset-password/reset', [
            'password' => 'new-password',
            'passwordConfirmation' => 'new-password',
        ]);

        self::assertResponseRedirects('/login', 303);
        $this->client->followRedirect();
        self::assertSame('Your password has been reset. You can now sign in.', $this->flash('success'));

        // The new password works, and the link cannot be used twice
        $this->visit('POST', '/login', ['email' => 'test@example.com', 'password' => 'new-password'], asForm: true);
        self::assertResponseRedirects('http://localhost/');
        $this->client->followRedirect();
        $this->page()->where('auth.user.email', 'test@example.com');

        $this->visit('GET', $link);
        $this->client->followRedirect();
        self::assertResponseRedirects('/reset-password');
    }

    public function testUnknownEmailGetsTheSameAnswerButNoEmail(): void
    {
        $this->visit('GET', '/reset-password');
        $this->visit('POST', '/reset-password', ['email' => 'nobody@example.com']);

        // Never reveal whether an account exists
        self::assertResponseRedirects('/reset-password/check-email', 303);
        self::assertEmailCount(0);
    }

    public function testMismatchedPasswordsComeBackAsErrors(): void
    {
        $this->visit('GET', '/reset-password');
        $this->visit('POST', '/reset-password', ['email' => 'test@example.com']);
        $link = $this->linkFromEmail('/reset-password/reset/');

        $this->visit('GET', $link);
        $this->client->followRedirect();
        $this->visit('POST', '/reset-password/reset', [
            'password' => 'new-password',
            'passwordConfirmation' => 'something-else',
        ]);

        self::assertResponseRedirects('http://localhost/reset-password/reset', 303);
        $this->client->followRedirect();
        $this->page()
            ->component('Auth/ResetPassword')
            ->where('errors.passwordConfirmation', 'The passwords do not match.');
    }
}
