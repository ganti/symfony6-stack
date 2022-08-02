#!/usr/bin/make -f

ENV = dev
ifdef env
ENV = $(env)
endif


LOCALTEST = false
ifdef localtest
LOCALTEST = $(localtest)
endif

DOCKER_COMPOSE_FILE ?= docker-compose.yml
DOCKER_CONTAINER_PHP = symfony6app-php


DOCKER_CMD = docker exec -t ${DOCKER_CONTAINER_PHP} sh -c

PHP_CMD = ${DOCKER_CMD} 'php
COMPOSER_CMD = ${DOCKER_CMD} 'php -d memory_limit=-1 /usr/bin/composer
SYMFONY_CMD = ${PHP_CMD} bin/console --env=${ENV} 
END = '

ifeq ($(ENV),prod)
	SYMFONY_CMD += --no-debug
endif


# Docker
inside:
	make docker-into-php
docker-into-php:
	docker exec -it ${DOCKER_CONTAINER_PHP} bash

docker-rebuild:
	docker-compose -f ${DOCKER_COMPOSE_FILE} up -d --force-recreate --build


# Composer
# Example: make composer command="update nothing"
COMPOSER_COMMAND =
ifdef command
COMPOSER_COMMAND = $(command)
endif

composer:
	${COMPOSER_CMD} ${COMPOSER_COMMAND} ${END}

dependencies-install:
	${COMPOSER_CMD} install ${END}

dependencies-update:
	${COMPOSER_CMD} update --with-all-dependencies ${END}

# Symfony
# Example: make symfony command=about
SYMFONY_COMMAND =
ifdef command
SYMFONY_COMMAND = $(command)
endif

symfony:
	${SYMFONY_CMD} ${SYMFONY_COMMAND} ${END}

setup-app:
	make composer-install
	${SYMFONY_CMD} doctrine:database:create  ${END} || true
	make db-migrations
	make db-fixtures
	make clear-cache
	make file-permissions

clear:
	rm -rf app/cache/${env}/*
	rm -rf app/logs/${env}/*
	${SYMFONY_CMD} cache:warmup ${END}

clear-all:
	rm -rf app/cache/*
	rm -rf app/logs/*

clear-cache:
	${SYMFONY_CMD} cache:clear ${END}
	${SYMFONY_CMD} cache:warmup ${END}

file-permissions:
	${DOCKER_CMD} 'chown -R www-data app/cache/ app/logs/ var/'


# Database
db-drop:
ifeq ($(ENV),prod)
	@echo "Dropping DB on production is disabled"
else
	${SYMFONY_CMD} doctrine:schema:drop --force --full-database ${END}
endif

db-make-migration:
	${SYMFONY_CMD} make:migration ${END}

db-migrations:
	${SYMFONY_CMD} doctrine:migrations:migrate --em default --no-interaction ${END}

db-fixtures:
	${SYMFONY_CMD} doctrine:fixtures:load -n -e dev ${END}