SHELL = /bin/sh

DOCKER					= docker
DOCKER_COMPOSE				= docker compose
DOCKER_COMPOSE_CI			= docker compose -f docker-compose.yml -f docker-compose.ci.yml
DOCKER_COMPOSE_EXEC			= $(DOCKER_COMPOSE) exec
DOCKER_COMPOSE_RUN			= $(DOCKER_COMPOSE) run --rm --remove-orphans
DOCKER_COMPOSE_EXEC_PHP		= $(DOCKER_COMPOSE_EXEC) php
DOCKER_COMPOSE_EXEC_PHP_CONSOLE	= $(DOCKER_COMPOSE_EXEC_PHP) bin/console
YARN					= $(DOCKER_COMPOSE) run --rm -u root node yarn

CONTAINERS_LIST						= "php"
# below is the prefix for each service directory being mounted on the yarn container
NODE_DIR_PATH_PREFIX                = /project/service-
NODE_CONTAINERS_RELATED_LIST_PATH	= $(NODE_DIR_PATH_PREFIX)app

NEW_DOCKER_COMPOSE_ENABLED			:= $(shell which docker compose)

define call_service_make
    $(MAKE) $(foreach container, $(CONTAINERS_LIST), $1-$(container))
endef

define service_command_arg
	@echo "Service:${GREEN}$1${RESET}";
    @$(DOCKER_COMPOSE_EXEC) $1 $2 || exit 1;
endef

define service_command
	@ for container in $(CONTAINERS_LIST); \
	do \
		echo "Service: ${GREEN}$$container${RESET}"; \
		$(DOCKER_COMPOSE_EXEC) $$container $1 || exit 1; \
	done
endef

define service_command_background
	@ for container in $(CONTAINERS_LIST); \
	do \
		echo "Service: ${GREEN}$$container${RESET}"; \
		$(DOCKER_COMPOSE_EXEC) -d $$container $1 || exit 1; \
	done
endef

define yarn_command_arg
	@echo "Service:${GREEN}$1${RESET}";
    $(YARN) --cwd=$(NODE_DIR_PATH_PREFIX)$1 $2 || exit 1;
endef

define yarn_command
	@ for container in $(NODE_CONTAINERS_RELATED_LIST_PATH); \
	do \
		echo "Service: ${GREEN}$$container${RESET}"; \
		$(YARN) --cwd=$$container $1 || exit 1; \
	done
endef

ifdef NEW_DOCKER_COMPOSE_ENABLED
	DOCKER_COMPOSE = docker compose
endif


ifndef CI_JOB_ID
	GREEN  := $(shell tput -Txterm setaf 2)
	YELLOW := $(shell tput -Txterm setaf 3)
	WHITE  := $(shell tput -Txterm setaf 7)
	RESET  := $(shell tput -Txterm sgr0)
	TARGET_MAX_CHAR_NUM=30
endif

ifdef CI_JOB_ID
	DOCKER_COMPOSE      = $(DOCKER_COMPOSE_CI)
	DOCKER_COMPOSE_EXEC = $(DOCKER_COMPOSE) exec -T
endif
