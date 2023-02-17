SHELL := /bin/bash

setup:
	symfony composer install
	npm install
	npm run build
	make start
	symfony console doctrine:database:create
	symfony console doctrine:migrations:migrate -n
.PHONY: setup

update:
	git pull
	symfony composer install
	npm install
	npm run build
	make restart
	symfony console doctrine:migrations:migrate -n
.PHONY: update

start:
	docker-compose up -d --remove-orphans
	symfony server:start -d
.PHONY: start

stop:
	symfony server:stop
	docker-compose down --remove-orphans
.PHONY: stop

restart: stop start
.PHONY: restart

test:
	symfony console doctrine:database:drop --force --env=test || true
	symfony console doctrine:database:create --env=test
	symfony console doctrine:migrations:migrate -n --env=test
	symfony console doctrine:fixtures:load -n --env=test
	symfony php bin/phpunit
.PHONY: test

lint:
	symfony php vendor/bin/ecs
	symfony php vendor/bin/phpstan --memory-limit=-1
.PHONY: lint

lint-fix:
	symfony php vendor/bin/ecs --fix
.PHONY: lint-fix

ci: lint test
.PHONY: ci
