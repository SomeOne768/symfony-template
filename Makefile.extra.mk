#################################
# Extra tools
#################################
cache:
	$(DOCKER_COMPOSE_EXEC_PHP) php bin/console cache:clear

cache-pool-clear:
	$(DOCKER_COMPOSE_EXEC_PHP) php bin/console c:p:c --all