#################################
# Extra tools
#################################
cache:
	docker compose exec php php bin/console cache:clear

cache-pool-clear:
	docker compose exec php php bin/console c:p:c --all