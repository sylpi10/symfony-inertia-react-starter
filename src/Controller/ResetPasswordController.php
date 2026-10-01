<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\ForgotPasswordInput;
use App\Dto\ResetPasswordInput;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route("/reset-password")]
final class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager,
        private Inertia $inertia,
    ) {}

    #[Route("", name: "app_forgot_password_request", methods: ["GET"])]
    public function request(): Response
    {
        return $this->inertia->render("Auth/ForgotPassword");
    }

    #[Route("", name: "app_forgot_password_request_submit", methods: ["POST"])]
    public function requestSubmit(
        #[MapRequestPayload] ForgotPasswordInput $input,
        MailerInterface $mailer,
    ): Response {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(["email" => $input->email]);

        // Never reveal whether an account exists: same response in every case.
        if (null !== $user) {
            try {
                $resetToken = $this->resetPasswordHelper->generateResetToken(
                    $user,
                );

                $mailer->send(
                    new TemplatedEmail()
                        ->from(
                            new Address(
                                "no-reply@example.com",
                                "Symfony Inertia Starter",
                            ),
                        )
                        ->to((string) $user->getEmail())
                        ->subject("Your password reset request")
                        ->htmlTemplate("reset_password/email.html.twig")
                        ->context([
                            "resetToken" => $resetToken,
                            "expiresInMinutes" => $this->tokenLifetimeInMinutes(),
                        ]),
                );
            } catch (ResetPasswordExceptionInterface) {
                // Too many requests for this user: stay silent, as for an unknown email.
            }
        }

        return $this->redirectToRoute(
            "app_check_email",
            status: Response::HTTP_SEE_OTHER,
        );
    }

    #[Route("/check-email", name: "app_check_email", methods: ["GET"])]
    public function checkEmail(): Response
    {
        return $this->inertia->render("Auth/CheckEmail", [
            "expiresInMinutes" => $this->tokenLifetimeInMinutes(),
        ]);
    }

    /**
     * The link from the email lands here with the token. It's moved to the session,
     * then we redirect to the same URL without it, so it doesn't leak (history, Referer…).
     */
    #[Route("/reset/{token}", name: "app_reset_password", methods: ["GET"])]
    public function reset(?string $token = null): Response
    {
        if (null !== $token) {
            $this->storeTokenInSession($token);

            return $this->redirectToRoute("app_reset_password");
        }

        if (null === $this->fetchUserFromSessionToken()) {
            return $this->redirectToRoute("app_forgot_password_request");
        }

        return $this->inertia->render("Auth/ResetPassword");
    }

    #[Route("/reset", name: "app_reset_password_submit", methods: ["POST"])]
    public function resetSubmit(
        #[MapRequestPayload] ResetPasswordInput $input,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $token = $this->getTokenFromSession();
        $user = $this->fetchUserFromSessionToken();

        if (null === $token || null === $user) {
            return $this->redirectToRoute(
                "app_forgot_password_request",
                status: Response::HTTP_SEE_OTHER,
            );
        }

        // A reset token must be used only once.
        $this->resetPasswordHelper->removeResetRequest($token);

        $user->setPassword(
            $passwordHasher->hashPassword($user, $input->password),
        );
        $this->entityManager->flush();

        $this->cleanSessionAfterReset();

        $this->inertia->flash(
            "success",
            "Your password has been reset. You can now sign in.",
        );

        return $this->redirectToRoute(
            "app_login",
            status: Response::HTTP_SEE_OTHER,
        );
    }

    private function fetchUserFromSessionToken(): ?User
    {
        $token = $this->getTokenFromSession();

        if (null === $token) {
            $this->inertia->flash(
                "error",
                "No reset link found. Please request a new one.",
            );

            return null;
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser(
                $token,
            );
        } catch (ResetPasswordExceptionInterface) {
            $this->inertia->flash(
                "error",
                "This reset link is invalid or has expired. Please request a new one.",
            );

            return null;
        }

        return $user instanceof User ? $user : null;
    }

    private function tokenLifetimeInMinutes(): int
    {
        return intdiv($this->resetPasswordHelper->getTokenLifetime(), 60);
    }
}
