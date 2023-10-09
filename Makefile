DOCKER_COMPOSE=docker compose -f docker-compose-dev.yml
DOCKER=docker

up:
	$(DOCKER_COMPOSE) up -d

down:
	$(DOCKER_COMPOSE) down -v

logs:
	$(DOCKER_COMPOSE) logs -f

ps:
	$(DOCKER_COMPOSE) ps

composer-install:
	$(DOCKER_COMPOSE) run --entrypoint="composer install" fpm-idelibre

composer-update:
	$(DOCKER_COMPOSE) run --entrypoint="composer update" fpm-idelibre

npm-install:
	$(DOCKER_COMPOSE) run --entrypoint="npm install" fpm-idelibre

npm-watch:
	$(DOCKER_COMPOSE) run --entrypoint="npm run watch" fpm-idelibre

npm-update:
	$(DOCKER_COMPOSE) run --entrypoint="npm update" fpm-idelibre

npm-run:
	$(DOCKER_COMPOSE) run --entrypoint="npm run dev" fpm-idelibre


dependency: composer-install npm-install npm-run

fpm-bash:
	$(DOCKER) exec -ti fpm-idelibre bash


ecs:
	$(DOCKER_COMPOSE) run --entrypoint="vendor/bin/ecs check --fix" fpm-idelibre
	$(DOCKER_COMPOSE) run --entrypoint="chown -R 1000:1000 /app/src" fpm-idelibre
