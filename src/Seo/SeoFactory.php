<?php

declare(strict_types=1);

namespace App\Seo;

use App\Dto\SeoDto;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SeoFactory
{
    /**
     * Shown when a link to the site is shared. Lives in public/, not in assets/:
     * Vite hashes the file names of assets/, so PHP cannot know their URL.
     */
    private const DEFAULT_IMAGE = '/og-image.png';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UrlHelper $urlHelper,
        #[Autowire('%app.site_name%')] private string $siteName,
    ) {
    }

    /**
     * A public page, meant to be indexed: the canonical URL is the route's absolute URL.
     *
     * @param array<string, mixed> $parameters
     */
    public function page(string $title, string $description, string $route, array $parameters = []): SeoDto
    {
        return new SeoDto(
            title: $this->title($title),
            description: $description,
            canonical: $this->urlGenerator->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL),
            image: $this->urlHelper->getAbsoluteUrl(self::DEFAULT_IMAGE),
        );
    }

    /**
     * A page search engines must ignore: authentication, admin, errors.
     */
    public function noindex(?string $title = null): SeoDto
    {
        return new SeoDto(
            title: null === $title ? $this->siteName : $this->title($title),
            noindex: true,
        );
    }

    private function title(string $title): string
    {
        return $title.' · '.$this->siteName;
    }
}
