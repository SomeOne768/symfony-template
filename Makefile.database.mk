#################################
# Colors
#################################
YELLOW := \033[0;33m
GREEN := \033[0;32m
RESET := \033[0m

#################################
# Docker
#################################

ifeq ($(CI),true)
MYSQL_EXEC := mysql -h 127.0.0.1 -u root -proot
PHP_EXEC := php
else
MYSQL_EXEC := docker compose exec -T mysql
PHP_EXEC := docker compose exec php
endif

#################################
# Databases
#################################
DB_NAME := symfony
DB_TEST_NAME := symfony_test

#################################
# Dump
#################################
DUMP_DIR := data/fixtures
DUMP_FILE := $(DUMP_DIR)/db.dump.sql

.PHONY: wait-mysql
wait-mysql:
	@echo "$(YELLOW)Waiting for MySQL to be ready...$(RESET)"
ifeq ($(CI),true)
	@until mysql -h 127.0.0.1 -u root -proot -e "SELECT 1;" >/dev/null 2>&1; do \
		echo "Waiting for MySQL..."; \
		sleep 1; \
	done
else
	@until $(MYSQL_EXEC) mysqladmin ping -h mysql -u root -proot --silent; do \
		sleep 1; \
	done
endif
	@echo "$(GREEN)MySQL is up$(RESET)"





#################################
# Doctrine DEV
#################################
.PHONY: db-drop db-create db-migrate db-reset

db-drop: wait-mysql
	$(PHP_EXEC) php bin/console doctrine:database:drop --if-exists --force

db-create: wait-mysql
	$(PHP_EXEC) php bin/console doctrine:database:create --if-not-exists

db-migrate:
	$(PHP_EXEC) php bin/console doctrine:migrations:migrate --no-interaction

db-reset: db-drop db-create db-migrate


#################################
# Doctrine TEST
#################################
.PHONY: db-test-drop db-test-create db-test-migrate db-test-reset

db-test-drop: wait-mysql
	$(PHP_EXEC) php bin/console doctrine:database:drop --env=test --if-exists --force

db-test-create: wait-mysql
	$(PHP_EXEC) php bin/console doctrine:database:create --env=test --if-not-exists

db-test-migrate:
	$(PHP_EXEC) php bin/console doctrine:migrations:migrate --env=test --no-interaction

db-test-reset: db-test-drop db-test-create db-test-migrate


#################################
# SQL dump
#################################
.PHONY: dump-generate dump-load dump-load-test

dump-generate: wait-mysql
	@mkdir -p $(DUMP_DIR)
	@echo "$(YELLOW)Generating dump from DEV database...$(RESET)"
	$(MYSQL_EXEC) mysqldump \
		--default-character-set=utf8mb4 \
		--skip-comments \
		--no-create-db \
		--no-create-info \
		--skip-triggers \
		--ignore-table=$(DB_NAME).doctrine_migration_versions \
		-u root -proot $(DB_NAME) \
		| sed 's/ AUTO_INCREMENT=[0-9]*//g' \
		> $(DUMP_FILE)
	@echo "$(GREEN)Dump generated: $(DUMP_FILE)$(RESET)"

dump-load: wait-mysql db-drop db-create db-migrate
	@echo "$(YELLOW)Loading dump into DEV database...$(RESET)"
	$(MYSQL_EXEC) mysql -u root -proot $(DB_NAME) < $(DUMP_FILE)
	@echo "$(GREEN)DEV database loaded$(RESET)"

dump-load-test: wait-mysql db-test-drop db-test-create db-test-migrate
	@echo "$(YELLOW)Loading dump into TEST database...$(RESET)"
	$(MYSQL_EXEC) mysql -u root -proot $(DB_TEST_NAME) < $(DUMP_FILE)
	@echo "$(GREEN)TEST database loaded$(RESET)"
