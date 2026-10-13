<?php

namespace App\Controller;

use App\Dto\Page\AboutPageProps;
use App\Seo\SeoFactory;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AboutController extends AbstractController
{
    public function __construct(
        private readonly Inertia $inertia,
        private readonly SeoFactory $seo,
    ) {}

    #[Route("/about", name: "app_about")]
    public function index(): Response
    {

        return $this->inertia->render(
            "About",
            get_object_vars(
                new AboutPageProps(
                    title: "About Page",
                    seo: $this->seo->page(
                        "About",
                        "What this starter includes: authentication, forms validated by Symfony, TypeScript types generated from PHP DTOs, and server-side rendering.",
                        "app_about",
                    ),
                ),
            ),
        );
    }
}
