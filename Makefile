.PHONY: help dev stop logs build test test-ssr docker-up docker-down docker-logs docker-fixtures docker-test

help:
	@echo "make dev    Symfony server + Vite (workers of .symfony.local.yaml)"
	@echo "make stop   stops server and his workers"
	@echo "make logs   follows logs"
	@echo "make build  builds Vite client + SSR (public/build, bootstrap/ssr)"
	@echo "make test   prepares the test database (migrations + fixtures), then runs PHPUnit"
	@echo "make test-ssr  builds the assets, then checks the home page is server-rendered (restart make dev afterwards)"
	@echo ""
	@echo "make docker-up        builds and starts the Docker stack (FrankenPHP + Vite)"
	@echo "make docker-down      stops the Docker stack (volumes are kept)"
	@echo "make docker-logs      follows the containers logs"
	@echo "make docker-fixtures  loads the demo data in the Docker database"
	@echo "make docker-test      runs PHPUnit in the php container"

dev:
	symfony server:start -d
	@symfony server:status

stop:
	symfony server:stop

logs:
	symfony server:log

build:
	npm ci
	npm run build

test:
	php bin/console doctrine:migrations:migrate -n --env=test
	php bin/console doctrine:fixtures:load -n --env=test
	php bin/phpunit

# The build replaces the files of the Vite dev server: restart `make dev` afterwards
test-ssr:
	npm run build
	sh tests/ssr-smoke-test.sh

# Containers run as the current user
DOCKER_COMPOSE = HOST_UID=$(shell id -u) HOST_GID=$(shell id -g) docker compose

docker-up:
	$(DOCKER_COMPOSE) up -d --build

docker-down:
	$(DOCKER_COMPOSE) down

docker-logs:
	$(DOCKER_COMPOSE) logs -f

docker-fixtures:
	$(DOCKER_COMPOSE) exec php php bin/console doctrine:fixtures:load -n

docker-test:
	$(DOCKER_COMPOSE) exec php sh -c "php bin/console doctrine:migrations:migrate -n --env=test && php bin/console doctrine:fixtures:load -n --env=test && php bin/phpunit"
