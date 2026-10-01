<?php

namespace App\Controller;

use App\Dto\Page\AboutPageProps;
use App\Service\TechStack;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AboutController extends AbstractController
{
    public function __construct(
        private readonly TechStack $techStack,
        private readonly Inertia $inertia,
    ) {}

    #[Route("/about", name: "app_about")]
    public function index(): Response
    {
        /**
         * @var list<string> $technos
         */ $technos = $this->techStack->getStack();

        return $this->inertia->render(
            "About",
            get_object_vars(
                new AboutPageProps(
                    title: "About Page",
                    para: "About: Symfony with React via Inertia.js, server-side rendered with Node",
                    technos: $this->techStack->getStack(),
                ),
            ),
        );
    }
}
