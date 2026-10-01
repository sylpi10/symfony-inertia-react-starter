<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

final class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
    ) {}

    public function sendConfirmation(User $user): void
    {
        $signature = $this->verifyEmailHelper->generateSignature(
            "app_verify_email",
            (string) $user->getId(),
            (string) $user->getEmail(),
            ["id" => $user->getId()],
        );

        $this->mailer->send(
            new TemplatedEmail()
                ->from(
                    new Address(
                        "no-reply@example.com",
                        "Symfony Inertia Starter",
                    ),
                )
                ->to((string) $user->getEmail())
                ->subject("Please confirm your email")
                ->htmlTemplate("registration/confirmation_email.html.twig")
                ->context([
                    "signedUrl" => $signature->getSignedUrl(),
                    "expiresInMinutes" => (int) round(
                        ($signature->getExpiresAt()->getTimestamp() - time()) /
                            60,
                    ),
                ]),
        );
    }
}
