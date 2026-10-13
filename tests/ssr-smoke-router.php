<?php

// Router for tests/ssr-smoke-test.sh. PHP's built-in server doesn't expose the
// environment variables in $_SERVER, where Symfony reads them: copy the ones the test sets.
foreach (['APP_ENV', 'INERTIA_SSR', 'APP_CACHE_DIR'] as $name) {
    if (false !== $value = getenv($name)) {
        $_SERVER[$name] = $_ENV[$name] = $value;
    }
}

$public = dirname(__DIR__).'/public';

// Let the built-in server send static files (built assets) itself
if (is_file($public.parse_url($_SERVER['REQUEST_URI'], \PHP_URL_PATH))) {
    return false;
}

$_SERVER['SCRIPT_FILENAME'] = $public.'/index.php';

require $public.'/index.php';
