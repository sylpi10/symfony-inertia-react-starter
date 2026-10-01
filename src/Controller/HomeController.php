<?php

namespace App\Controller;

use App\Dto\Page\HomePageProps;
use App\Dto\TechnoDto;
use App\Repository\TechnoRepository;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly Inertia $inertia,
        private readonly TechnoRepository $technoRepository
    ) {}

    #[Route("/", name: "app_home")]
    public function index(): Response
    {
        return $this->inertia->render(
            "Home",
            get_object_vars(
                new HomePageProps(
                    title: "Home Page",
                    para: "Home: Symfony with React via Inertia.js, server-side rendered with Node",
                    technos: array_map(TechnoDto::fromEntity(...),
                        $this->technoRepository->findAllOrdered()),
                        // eq: array_map(fn (Techno $techno) => TechnoDto::fromEntity($techno), $technos);
                ),
            ),
        );
    }
}
