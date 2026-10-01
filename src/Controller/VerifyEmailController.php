<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

final class VerifyEmailController extends AbstractController
{
    public function __construct(private Inertia $inertia) {}

    #[Route("/verify/email", name: "app_verify_email", methods: ["GET"])]
    public function verify(
        Request $request,
        UserRepository $userRepository,
        VerifyEmailHelperInterface $verifyEmailHelper,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $userRepository->find($request->query->getInt("id"));

        if (null === $user) {
            $this->inertia->flash(
                "error",
                "This verification link is invalid.",
            );

            return $this->redirectToRoute("app_home");
        }

        try {
            $verifyEmailHelper->validateEmailConfirmationFromRequest(
                $request,
                (string) $user->getId(),
                (string) $user->getEmail(),
            );
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->inertia->flash("error", $exception->getReason());

            return $this->redirectToRoute("app_home");
        }

        $user->setIsVerified(true);
        $entityManager->flush();

        $this->inertia->flash(
            "success",
            "Your email address has been verified.",
        );

        return $this->redirectToRoute("app_home");
    }

    #[Route("/verify/resend", name: "app_verify_resend", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED")]
    public function resend(EmailVerifier $emailVerifier): Response
    {
        $user = $this->getUser();

        if ($user instanceof User && !$user->isVerified()) {
            $emailVerifier->sendConfirmation($user);
            $this->inertia->flash(
                "success",
                "A new verification link has been sent to your email.",
            );
        }

        return $this->redirectToRoute(
            "app_home",
            status: Response::HTTP_SEE_OTHER,
        );
    }
}
