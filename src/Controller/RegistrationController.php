<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\RegisterUserInput;
use App\Entity\User;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationController extends AbstractController
{
    public function __construct(private Inertia $inertia) {}

    #[Route("/register", name: "app_register", methods: ["GET"])]
    public function show(): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute("app_home");
        }

        return $this->inertia->render("Auth/Register");
    }

    #[Route("/register", name: "app_register_submit", methods: ["POST"])]
    public function submit(
        #[MapRequestPayload] RegisterUserInput $input,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        Security $security,
        EmailVerifier $emailVerifier,
    ): Response {
        $user = new User();
        $user->setEmail($input->email);
        $user->setPassword(
            $passwordHasher->hashPassword($user, $input->password),
        );

        $entityManager->persist($user);
        $entityManager->flush();

        $emailVerifier->sendConfirmation($user);

        $security->login($user, "form_login", "main");

        $this->inertia->flash(
            "success",
            "Welcome! Check your inbox to confirm your email address.",
        );

        return $this->redirectToRoute(
            "app_home",
            status: Response::HTTP_SEE_OTHER,
        );
    }
}
