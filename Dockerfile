# syntax=docker/dockerfile:1

# Development images only. For production, see docs/ssr-and-production.md.

FROM dunglas/frankenphp:1-php8.4 AS php_dev

RUN install-php-extensions @composer intl opcache pdo_sqlite zip

# The container runs as your host user (see compose.yaml), which has no home
# directory in the image: point every tool that writes files to /tmp.
# APP_CACHE_DIR keeps the Symfony cache out of var/, so it never clashes with
# the cache of a server started on the host (`make dev`).
ENV HOME=/tmp \
    COMPOSER_HOME=/tmp/composer \
    XDG_CONFIG_HOME=/tmp/caddy/config \
    XDG_DATA_HOME=/tmp/caddy/data \
    APP_CACHE_DIR=/tmp/symfony/cache

# SQLite database: the named volume mounted here inherits these permissions
RUN mkdir -p /srv/data && chmod 1777 /srv/data

COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/app-entrypoint

ENTRYPOINT ["app-entrypoint"]
CMD ["--config", "/etc/frankenphp/Caddyfile", "--adapter", "caddyfile"]


FROM node:24-slim AS node_dev

# node_modules lives in a named volume (native binaries differ between the host
# and the container); it inherits these permissions.
RUN mkdir -p /app/node_modules && chmod 1777 /app/node_modules

ENV HOME=/tmp
WORKDIR /app
