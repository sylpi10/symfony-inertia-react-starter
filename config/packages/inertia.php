<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension("inertia", [
        "root_view" => "base.html.twig",

        // Read at container compile time: the bundle decides here whether to wire the SSR HTTP client.
        "ssr_enabled" => filter_var(
            $_SERVER["INERTIA_SSR"] ?? false,
            \FILTER_VALIDATE_BOOL,
        ),
        "ssr_url" => "%env(INERTIA_SSR_URL)%",
        "ssr_bundle" => "%kernel.project_dir%/bootstrap/ssr/ssr.js",

        "pages" => [
            "ensure_pages_exist" => true,
            "paths" => ["%kernel.project_dir%/assets/Pages"],
        ],
    ]);
};
