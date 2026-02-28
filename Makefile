-include Makefile.common.mk
-include Makefile.database.mk
-include Makefile.git.mk
-include Makefile.extra.mk

#################################
# Initialization
#################################
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
	$(PHP_EXEC) composer $(cmd)

npm:
	$(NPM_EXEC) $(cmd)

#################################
# Yarn / Node commands
#################################
.PHONY: yarn yarn-dev yarn-watch yarn-add

yarn:
	$(YARN_EXEC) install

yarn-dev:
	$(YARN_EXEC) dev

yarn-watch:
	$(YARN_EXEC) watch

yarn-add:
	$(YARN_EXEC) add $(packages)

#################################
# Symfony / Quality tools
#################################
.PHONY: phpstan twigcs rector rector-dry php-cs-fixer php-cs-fixer-dry php-arkitect vendor

phpstan:
	$(PHP_EXEC) vendor/bin/phpstan analyse --memory-limit=2G

twigcs:
	$(PHP_EXEC) vendor/bin/twigcs templates

rector:
	$(PHP_EXEC) vendor/bin/rector process src

rector-dry:
	$(PHP_EXEC) vendor/bin/rector process src --dry-run

php-cs-fixer:
	$(PHP_EXEC) vendor/bin/php-cs-fixer fix --allow-risky=yes

php-cs-fixer-dry:
	$(PHP_EXEC) vendor/bin/php-cs-fixer fix --allow-risky=yes --dry-run --diff

php-arkitect:
	$(PHP_EXEC) vendor/bin/phparkitect check

vendor:
	$(PHP_EXEC) composer install --prefer-dist --no-interaction
	$(NPM_EXEC) install
	$(YARN_EXEC) dev

#################################
# Tests
#################################
.PHONY: phpunit phpunit-functional behat

phpunit:
	$(PHP_EXEC) php bin/phpunit --colors=always

phpunit-functional:
	$(PHP_EXEC) php bin/phpunit --colors=always --group functional

behat:
	$(PHP_EXEC) php vendor/bin/behat --config=behat.yml --format=progress --strict

#################################
# QA / Code quality
#################################
.PHONY: qa-core qa-test qa-full

qa-core: php-cs-fixer rector phpstan php-arkitect twigcs

qa-test: phpunit phpunit-functional behat

qa-full: qa-core qa-test