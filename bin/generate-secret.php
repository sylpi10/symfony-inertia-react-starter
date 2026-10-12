<?php

// Replaces APP_SECRET in .env.dev with a random value, so that no two projects
// created from this starter share the same secret. Run by `composer setup`.

$file = dirname(__DIR__).'/.env.dev';
$env = preg_replace(
    '/^APP_SECRET=.*$/m',
    'APP_SECRET='.bin2hex(random_bytes(16)),
    (string) file_get_contents($file),
    1,
    $count,
);

if (1 !== $count) {
    fwrite(\STDERR, "No APP_SECRET line found in .env.dev\n");
    exit(1);
}

file_put_contents($file, $env);
echo "Generated a new APP_SECRET in .env.dev\n";
