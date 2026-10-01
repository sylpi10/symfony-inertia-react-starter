<?php

namespace App\Controller;

use App\Dto\Page\AboutPageProps;
use App\Dto\TechnoDto;
use App\Repository\TechnoRepository;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AboutController extends AbstractController
{
    public function __construct(
        private readonly Inertia $inertia,
        private readonly TechnoRepository $technoRepository
    ) {}

    #[Route("/about", name: "app_about")]
    public function index(): Response
    {
        return $this->inertia->render(
            "About",
            get_object_vars(
                new AboutPageProps(
                    title: "About Page",
                    para: "About: Symfony with React via Inertia.js, server-side rendered with Node",
                    technos: array_map(TechnoDto::fromEntity(...),
                        $this->technoRepository->findAllOrdered()),
                        // array_map(fn (Techno $techno) => TechnoDto::fromEntity($techno), $technos);
                ),
            ),
        );
    }
}
