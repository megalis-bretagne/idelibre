

echo "init databases with symfony"
./bin/console doctrine:migrations:migrate --no-interaction
./bin/console initBdd docker-resources/minimum.sql
./bin/console cache:clear --no-interaction

php-fpm
