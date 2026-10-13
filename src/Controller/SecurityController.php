<?php

namespace App\Controller;

use App\Seo\SeoFactory;
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
        SeoFactory $seo,
    ): Response {
        $props = [
            "lastEmail" => $authenticationUtils->getLastUsername(),
            "seo" => $seo->noindex("Sign in"),
        ];

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

    #[Route("/logout", name: "app_logout", methods: ["POST"])]
    public function logout(): never
    {
        throw new \LogicException(
            "Intercepted by the logout key of the firewall.",
        );
    }
}
