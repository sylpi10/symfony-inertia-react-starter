<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SitemapController extends AbstractController
{
    /**
     * Routes of the public pages search engines should index: add yours here.
     * For pages coming from the database, pass their URLs to the template as well.
     */
    private const ROUTES = ["app_home", "app_about"];

    #[Route("/sitemap.xml", name: "app_sitemap", methods: ["GET"])]
    public function sitemap(): Response
    {
        $response = $this->render("seo/sitemap.xml.twig", ["routes" => self::ROUTES]);
        $response->headers->set("Content-Type", "application/xml; charset=UTF-8");

        return $response;
    }

    #[Route("/robots.txt", name: "app_robots", methods: ["GET"])]
    public function robots(): Response
    {
        $response = $this->render("seo/robots.txt.twig");
        $response->headers->set("Content-Type", "text/plain; charset=UTF-8");

        return $response;
    }
}
