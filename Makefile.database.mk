#################################
# Colors
#################################
YELLOW := \033[0;33m
GREEN  := \033[0;32m
RESET  := \033[0m

#################################
# Docker
#################################
PHP_EXEC   := docker compose exec php
MYSQL_EXEC := docker compose exec -T mysql

#################################
# MySQL
#################################

.PHONY: wait-mysql

wait-mysql:
	@echo "$(YELLOW)Waiting for MySQL to be ready...$(RESET)"
	@until $(MYSQL_EXEC) mysqladmin ping -h localhost -u root -proot --silent; do \
		sleep 1; \
	done
	@echo "$(GREEN)MySQL is up$(RESET)"

#################################
# Doctrine databases
#################################

.PHONY: db-drop db-create db-migrate db-reset
.PHONY: db-test-drop db-test-create db-test-migrate db-test-reset

## DEV database
db-drop: wait-mysql
	$(PHP_EXEC) php bin/console doctrine:database:drop --if-exists --force

db-create: wait-mysql
	$(PHP_EXEC) php bin/console doctrine:database:create --if-not-exists

db-migrate:
	$(PHP_EXEC) php bin/console doctrine:migrations:migrate --no-interaction

db-reset: db-drop db-create db-migrate

## TEST database
db-test-drop: wait-mysql
	$(PHP_EXEC) php bin/console doctrine:database:drop --env=test --if-exists --force

db-test-create: wait-mysql
	$(PHP_EXEC) php bin/console doctrine:database:create --env=test --if-not-exists

db-test-migrate:
	$(PHP_EXEC) php bin/console doctrine:migrations:migrate --env=test --no-interaction

db-test-reset: db-test-drop db-test-create db-test-migrate

#################################
# SQL dump strategy
#################################

DUMP_PATH := /var/www/html/data/fixtures/test_dump.sql

.PHONY: dump-generate dump-load dump-load-test

## Generate dump from DEV database
dump-generate: wait-mysql
	@mkdir -p data/fixtures
	docker compose exec -T mysql \
		mysqldump \
		--default-character-set=utf8mb4 \
		--skip-comments \
		--no-create-db \
		--no-create-info \
		--skip-triggers \
		--ignore-table=symfony.doctrine_migration_versions \
		-u root -proot symfony \
		> data/fixtures/load_dump.sql
	@echo "$(GREEN)Dump generated at data/fixtures/load_dump.sql$(RESET)"


## Load dump into DEV database
dump-load: wait-mysql db-drop db-create
	docker compose exec -T mysql \
		mysql -u root -proot symfony \
		< data/fixtures/load_dump.sql
	@echo "$(GREEN)DEV database loaded from dump$(RESET)"

## Load dump into TEST database
dump-load-test: wait-mysql db-test-drop db-test-create
	docker compose exec -T mysql \
		mysql -u root -proot symfony_test \
		< data/fixtures/load_dump.sql
	@echo "$(GREEN)TEST database loaded from dump$(RESET)"



#################################
# Fixtures (optional / legacy)
#################################

.PHONY: fixtures fixtures-test

fixtures: wait-mysql
	$(PHP_EXEC) php bin/console doctrine:fixtures:load --env=dev --no-interaction

fixtures-test: wait-mysql
	$(PHP_EXEC) php bin/console doctrine:fixtures:load --env=test --no-interaction
