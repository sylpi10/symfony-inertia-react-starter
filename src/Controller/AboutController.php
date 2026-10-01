<?php

namespace App\Controller;

use App\Dto\Page\AboutPageProps;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AboutController extends AbstractController
{
    public function __construct(
        private readonly Inertia $inertia,
    ) {}

    #[Route("/about", name: "app_about")]
    public function index(): Response
    {

        return $this->inertia->render(
            "About",
            get_object_vars(
                new AboutPageProps(
                    title: "About Page",
                ),
            ),
        );
    }
}
