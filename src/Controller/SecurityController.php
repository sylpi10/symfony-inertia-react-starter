<?php

namespace App\Controller;

use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    #[Route("/login", name: "app_login")]
    public function login(
        Inertia $inertia,
        AuthenticationUtils $authenticationUtils,
    ): Response {
        $props = ["lastEmail" => $authenticationUtils->getLastUsername()];

        $error = $authenticationUtils->getLastAuthenticationError();
        if (null !== $error) {
            $props["errors"] = [
                "email" => strtr(
                    $error->getMessageKey(),
                    $error->getMessageData(),
                ),
            ];
        }

        return $inertia->render("Auth/Login", $props);
    }
}
