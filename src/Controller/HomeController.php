<?php

namespace App\Controller;

use App\Dto\Page\HomePageProps;
use App\Dto\TechnoDto;
use App\Repository\TechnoRepository;
use App\Seo\SeoFactory;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly Inertia $inertia,
        private readonly TechnoRepository $technoRepository,
        private readonly SeoFactory $seo,
    ) {}

    #[Route("/", name: "app_home")]
    public function index(): Response
    {
        return $this->inertia->render(
            "Home",
            get_object_vars(
                new HomePageProps(
                    title: "Home Page",
                    para: "A Symfony 8 starter with a React 19 + TypeScript,
                    Inertia.js with SSR support",
                    technos: array_map(TechnoDto::fromEntity(...),
                        $this->technoRepository->findAllOrdered()),
                        // eq: array_map(fn (Techno $techno) => TechnoDto::fromEntity($techno), $technos);
                    seo: $this->seo->page(
                        "Home",
                        "A Symfony 8 starter with a React 19 + TypeScript front end, glued together by Inertia.js, with SSR support.",
                        "app_home",
                    ),
                ),
            ),
        );
    }
}
