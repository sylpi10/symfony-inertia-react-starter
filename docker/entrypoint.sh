#!/bin/sh
set -e

# Only when starting the web server, not for one-off commands (`docker compose run php ...`)
if [ "${1#-}" != "$1" ] || [ "$1" = "frankenphp" ]; then
    composer install --no-interaction --prefer-dist --no-progress
    # Never block the start on a failing migration: you need the container up to fix it
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration \
        || echo "WARNING: migrations failed, the app starts anyway (see the error above)."
fi

exec docker-php-entrypoint "$@"
