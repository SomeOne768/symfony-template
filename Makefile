-include Makefile.common.mk
-include Makefile.database.mk
-include Makefile.git.mk
-include Makefile.extra.mk

#################################
# Initialization
#################################
# On utilise les variables pour que l'init fonctionne aussi en CI si besoin
init: pull build up vendor yarn db-create dump-load

#################################
# Docker compose management
#################################
.PHONY: pull build start up down restart logs
pull:
	$(DOCKER_COMPOSE) pull

build:
	$(DOCKER_COMPOSE) build

start:
	$(DOCKER_COMPOSE) up -d

up:
	$(DOCKER_COMPOSE) up -d --force-recreate --remove-orphans

down:
	$(DOCKER_COMPOSE) down

logs:
	$(DOCKER_COMPOSE) logs -f

restart: down up

#################################
# Composer & NPM commands
#################################
.PHONY: composer npm
composer:
	$(DOCKER_COMPOSE_EXEC_PHP) composer $(cmd)

npm:
	$(DOCKER_COMPOSE_EXEC) node npm $(cmd)

#################################
# Yarn / Node commands
#################################
.PHONY: yarn yarn-dev yarn-watch yarn-add

# Ici on utilise ta variable YARN qui fait un "run --rm"
yarn:
	$(YARN) install

yarn-dev:
	$(YARN) dev

yarn-watch:
	$(YARN) watch

yarn-add:
	$(YARN) add $(packages)

#################################
# Symfony / Quality tools
#################################
.PHONY: phpstan twigcs rector rector-dry php-cs-fixer php-cs-fixer-dry php-arkitect vendor
phpstan:
	$(DOCKER_COMPOSE_EXEC_PHP) vendor/bin/phpstan analyse --memory-limit=2G

twigcs:
	$(DOCKER_COMPOSE_EXEC_PHP) vendor/bin/twigcs templates

rector:
	$(DOCKER_COMPOSE_EXEC_PHP) vendor/bin/rector process src

rector-dry:
	$(DOCKER_COMPOSE_EXEC_PHP) vendor/bin/rector process src --dry-run

php-cs-fixer:
	$(DOCKER_COMPOSE_EXEC_PHP) php vendor/bin/php-cs-fixer fix --allow-risky=yes

php-cs-fixer-dry:
	$(DOCKER_COMPOSE_EXEC_PHP) php vendor/bin/php-cs-fixer fix --allow-risky=yes --dry-run --diff

php-arkitect:
	$(DOCKER_COMPOSE_EXEC_PHP) php vendor/bin/phparkitect check

vendor:
	$(DOCKER_COMPOSE_EXEC_PHP) composer install --prefer-dist --no-interaction
	$(DOCKER_COMPOSE_EXEC) node npm install
	$(YARN) dev

#################################
# Tests
#################################
.PHONY: phpunit phpunit-functional behat

phpunit:
	$(DOCKER_COMPOSE_EXEC_PHP) php bin/phpunit --colors=always

phpunit-functional:
	$(DOCKER_COMPOSE_EXEC_PHP) php bin/phpunit --colors=always --group functional

behat:
	$(DOCKER_COMPOSE_EXEC_PHP) php vendor/bin/behat --config=behat.yml --format=progress --strict

#################################
# QA / Code quality
#################################
.PHONY: qa-core qa-test qa-core-full
qa-core: php-cs-fixer rector phpstan arkitect twigcs

qa-test: phpunit phpunit-functional behat

qa-full: qa-core qa-test
