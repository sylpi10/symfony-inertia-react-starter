<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    // Read at container compile time: the bundle decides here whether to wire the SSR HTTP client.
    $ssrEnabled = filter_var($_SERVER["INERTIA_SSR"] ?? false, \FILTER_VALIDATE_BOOL);

    // Asset version: a hash of Vite's build manifest. When it changes (new deploy),
    // Inertia forces a full page reload so browsers stop running the old bundle.
    // No manifest (Vite dev server): no versioning.
    $manifest = \dirname(__DIR__, 2) . "/public/build/.vite/manifest.json";

    $container->extension("inertia", [
        "root_view" => "base.html.twig",
        "version" => is_file($manifest) ? hash_file("xxh128", $manifest) : null,
        "ssr_enabled" => $ssrEnabled,
        // Only reference the env var when SSR is on: an unused env var makes the container compilation fail.
        "ssr_url" => $ssrEnabled ? "%env(INERTIA_SSR_URL)%" : "http://127.0.0.1:13714",
        "ssr_bundle" => "%kernel.project_dir%/bootstrap/ssr/ssr.js",

        "pages" => [
            "ensure_pages_exist" => true,
            "paths" => ["%kernel.project_dir%/assets/Pages"],
        ],
    ]);
};
