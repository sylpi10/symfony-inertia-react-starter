<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use App\Tests\InertiaWebTestCase;

final class VerifyEmailTest extends InertiaWebTestCase
{
    public function testSignedLinkVerifiesTheUser(): void
    {
        $link = $this->registerAndGetLink();

        $this->visit('GET', $link);

        self::assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->page()->where('auth.user.isVerified', true);
        self::assertSame('Your email address has been verified.', $this->flash('success'));
    }

    public function testTamperedLinkIsRejected(): void
    {
        $link = $this->registerAndGetLink();

        $this->visit('GET', $link.'tampered');

        self::assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->page()->where('auth.user.isVerified', false);
        self::assertNotNull($this->flash('error'));
    }

    public function testResendSendsANewEmailToUnverifiedUsersOnly(): void
    {
        $this->registerAndGetLink();

        $this->visit('POST', '/verify/resend');
        self::assertResponseRedirects('/', 303);
        self::assertEmailCount(1);

        // Already verified: nothing is sent
        $this->loginAs('test@example.com');
        $this->visit('GET', '/');
        $this->visit('POST', '/verify/resend');
        self::assertEmailCount(0);
    }

    /**
     * Registers a new user (logged in right away) and returns the link from the confirmation email.
     */
    private function registerAndGetLink(): string
    {
        $this->visit('GET', '/register');
        $this->visit('POST', '/register', [
            'email' => 'new@example.com',
            'password' => 'password1',
            'passwordConfirmation' => 'password1',
        ]);

        $link = $this->linkFromEmail('/verify/email');

        $user = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'new@example.com']);
        self::assertFalse($user->isVerified());

        return $link;
    }
}
