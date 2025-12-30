.PHONY: all init composer npm fix phpstan rector twigcs arkitect qa assets-dev assets-watch db migrate

init: composer npm db migrate assets

composer:
	docker compose exec php composer $(cmd)

db:
	docker compose exec php php bin/console doctrine:database:create --if-not-exists

migrate:
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

yarn:
	docker compose exec node npm run dev

yarn-dev:
	docker compose exec node yarn dev

yarn-watch:
	docker compose exec node yarn watch

up:
	docker compose up -d --force-recreate --remove-orphans

down:
	docker compose down

restart: down up

build:
	docker compose build

logs:
	docker compose logs -f

bash:
	docker compose exec php bash

composer:
	docker compose exec php composer $(cmd)

npm:
	docker compose exec node npm $(cmd)

cache:
	docker compose exec php php bin/console cache:clear

phpstan:
	docker compose exec php vendor/bin/phpstan analyse

twigcs:
	docker compose exec php vendor/bin/twigcs templates

rector:
	docker compose exec php vendor/bin/rector process src --dry-run

php-cs-fixer:
	docker compose exec php php /var/www/html/vendor/bin/php-cs-fixer fix

arkitect:
	docker compose exec php php /var/www/html/vendor/bin/phparkitect check

qa-core: php-cs-fixer rector phpstan arkitect twigcs
