SHELL := /bin/bash

setup:
	symfony composer install
	make start
	symfony console doctrine:database:create --if-not-exists
	symfony console doctrine:migrations:migrate -n
.PHONY: setup

update:
	git pull
	symfony composer install
	make restart
	symfony console doctrine:migrations:migrate -n
.PHONY: update

start:
	symfony server:start -d
.PHONY: start

stop:
	symfony server:stop
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

fix:
	symfony php vendor/bin/ecs --fix
.PHONY: fix

ci: lint test
.PHONY: ci
