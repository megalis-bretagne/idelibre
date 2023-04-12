#!/bin/bash

## -f to force eveything !

## set the environement file you want to use
source ./.env

force=false
#get the options
while getopts "f" option; do
    case "${option}" in
        f)
            force=true
            ;;
    esac
done

#check if file exists
FILE=./docker-resources/nginx.vhost
if [ ! -f "$FILE" ] || [ $force = true ]; then
  echo "### Generate vhost nginx "
  cp ./docker-resources/nginx_template.vhost ./docker-resources/nginx.vhost
  sed -i -e"s|URL|$URL|" ./docker-resources/nginx.vhost
fi


if ! [ -x "$(command -v docker-compose)" ]; then
  echo 'Error: docker-compose is not installed.' >&2
  exit 1
fi

domains=($URL)
rsa_key_size=4096
data_path="/data/certbot"
email="${EMAIL}" # Adding a valid address is strongly recommended
staging=$STAGGING_LETSENCRYPT #Set to 1 if you're testing your setup to avoid hitting request limits

if [ -d "$data_path" ]; then
 # read -p "Existing data found for $domains. Continue and replace existing certificate? (y/N) " decision
  if [ $force != true ] ; then
    echo "### Certificate already exists. use -f to replace"
    docker-compose down
    docker-compose up -d
    exit
  fi
fi


if [ ! -e "$data_path/conf/options-ssl-nginx.conf" ] || [ ! -e "$data_path/conf/ssl-dhparams.pem" ]; then
  echo "### COPY TLS parameters ..."
  mkdir -p "$data_path/conf"
  cat ./docker-resources/options-ssl-nginx.conf > "$data_path/conf/options-ssl-nginx.conf"
  cat  ./docker-resources/ssl-dhparams.pem > "$data_path/conf/ssl-dhparams.pem"
  echo
fi


echo "### Creating dummy certificate for $domains ..."
path="/etc/letsencrypt/live/$domains"
mkdir -p "$data_path/conf/live/$domains"
docker-compose run --rm --entrypoint "\
    openssl req -x509 -nodes -newkey rsa:$rsa_key_size -days 365\
    -keyout '$path/privkey.pem' \
    -out '$path/fullchain.pem' \
    -subj '/CN=localhost'" certbot
echo


if [ $SELF_SIGNED = 1 ]; then
  echo "### WORKING WITH SELF SIGNED CERTIFICATE"
  docker-compose down
  docker-compose up -d

exit 0
fi


echo "### From here generate letsencrypt certificate (if stagging=1 we only try)"


echo "### Starting nginx ..."
docker-compose up --force-recreate -d nginx-idelibre
echo


echo "### Deleting dummy certificate for $domains ..."
docker-compose run --rm --entrypoint "\
  rm -Rf /etc/letsencrypt/live/$domains && \
  rm -Rf /etc/letsencrypt/archive/$domains && \
  rm -Rf /etc/letsencrypt/renewal/$domains.conf" certbot
echo


echo "### Requesting Let's Encrypt certificate for $domains ..."
#Join $domains to -d args
domain_args=""
for domain in "${domains[@]}"; do
  domain_args="$domain_args -d $domain"
done

# Select appropriate email arg
case "$email" in
  "") email_arg="--register-unsafely-without-email" ;;
  *) email_arg="--email $email" ;;
esac

# Enable staging mode if needed
if [ $staging != "0" ]; then staging_arg="--staging"; fi

docker-compose run --rm --entrypoint "\
  certbot certonly --webroot -w /var/www/certbot \
    $staging_arg \
    $email_arg \
    $domain_args \
    --rsa-key-size $rsa_key_size \
    --agree-tos --non-interactive \
    --force-renewal" certbot
echo

echo "### Reloading nginx ..."
docker-compose exec nginx-idelibre nginx -s reload
