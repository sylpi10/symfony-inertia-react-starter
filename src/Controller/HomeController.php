<?php

namespace App\Controller;

use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route("/", name: "home")]
    public function index(Inertia $inertia): Response
    {
        $technos = [
            "Symfony",
            "InertiaJs",
            "React",
            "Typescript",
            "SSR",
            "Sass",
        ];

        return $inertia->render("Home", [
            "message" => "Hello from Symfony",
            "para" => "Symfony skeleton with InertiaJs and React configuration",
            "technos" => $technos,
        ]);
    }
}
