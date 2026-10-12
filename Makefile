.PHONY: help dev stop logs build test

help:
	@echo "make dev    Symfony server + Vite (workers of .symfony.local.yaml)"
	@echo "make stop   stops server and his workers"
	@echo "make logs   follows logs"
	@echo "make build  builds Vite client + SSR (public/build, bootstrap/ssr)"
	@echo "make test   prepares the test database (migrations + fixtures), then runs PHPUnit"

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
