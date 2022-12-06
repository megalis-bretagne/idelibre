#!/bin/bash

echo "init databases with symfony"
./bin/console migrate:from40
./bin/console doctrine:migrations:migrate --no-interaction
./bin/console initBdd docker-resources/minimum.sql
./bin/console cache:clear --no-interaction

USER=$(stat -c '%U' /data)
if [ "$USER" != "www-data" ]; then
  chown -R www-data: /data
fi

php-fpm8.1 --nodaemonize

