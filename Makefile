-include Makefile.database.mk

#################################
# Phony targets
#################################
.PHONY: all init pull composer npm fix phpstan rector twigcs arkitect qa assets-dev assets-watch db migrate test up down restart build logs bash vendor

#################################
# Initialization
#################################
init: pull build up composer npm create-db migrate yarn

#################################
# Docker compose management
#################################
pull:
	docker compose pull

build:
	docker compose build

start:
	docker compose up -d

up:
	docker compose up -d --force-recreate --remove-orphans

down:
	docker compose down

restart: down up

logs:
	docker compose logs -f


#################################
# Composer & NPM commands
#################################
composer:
	docker compose exec php composer $(cmd)

npm:
	docker compose exec node npm $(cmd)

#################################
# Yarn / Node commands
#################################
yarn:
	docker compose exec node yarn install

yarn-dev:
	docker compose exec node yarn dev

yarn-watch:
	docker compose exec node yarn watch

#################################
# Symfony / Quality tools
#################################
cache:
	docker compose exec php php bin/console cache:clear

phpstan:
	docker compose exec php vendor/bin/phpstan analyse --memory-limit=2G #-1

twigcs:
	docker compose exec php vendor/bin/twigcs templates

rector:
	docker compose exec php vendor/bin/rector process src --dry-run

php-cs-fixer:
	docker compose exec php php vendor/bin/php-cs-fixer fix

arkitect:
	docker compose exec php php vendor/bin/phparkitect check

vendor:
	docker compose exec php composer install --prefer-dist --no-interaction
	docker compose exec node npm install
	docker compose exec node yarn dev

#################################
# QA / Code quality
#################################
qa-core: php-cs-fixer rector phpstan arkitect twigcs

qa-core-full: qa-core behat phpunit

#################################
# Tests
#################################
phpunit:
	docker compose exec php php bin/phpunit --colors=always

behat:
	docker compose exec php php vendor/bin/behat --config=behat.yml --format=progress --strict
