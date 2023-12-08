#!/bin/bash

il_path="/opt/idelibre/dist"
il_current_path="/opt/idelibre/current"

## prequisites checks
#check if docker-compose-plugin is installed
if [ ! -e "/usr/libexec/docker/cli-plugins/docker-compose" ]; then
    echo -e "Please check if docker-compose-plugin is installed on this system. \nStopping ⛔️"
    exit 253
fi
#check from where the script is executed
if [ ! "$(pwd)" = "${il_current_path}" ]; then
    echo -e "Please change your working directory to /opt/idelibre/current \nStopping ⛔️"
    exit 254
    elif [ ! -L "${il_current_path}/docker-compose.yml" ] || [ ! -e "${il_current_path}/.env" ]; then
        echo -e "One of the needed files does not exist in ${il_current_path} : docker-compose.yml, .env \nStopping ⛔️"
        exit 255
fi

## set the environement file you want to use
source .env

## -f to force eveything !
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
nginx_file="${il_path}/docker-resources/nginx.vhost"
nginx_tpl_file="${il_path}/docker-resources/nginx_template.vhost"
if [ ! -f "${nginx_file}" ] || [ $force = true ]; then
  echo "### Generate vhost nginx"
  cp -a ${nginx_tpl_file} ${nginx_file}
  sed -i -e"s|URL|$URL|" ${nginx_file}
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
    docker compose down
    docker compose up -d
    exit
  fi
fi


if [ ! -e "$data_path/conf/options-ssl-nginx.conf" ] || [ ! -e "$data_path/conf/ssl-dhparams.pem" ]; then
  echo "### COPY TLS parameters ..."
  mkdir -p "$data_path/conf"
  cat "${il_path}/docker-resources/options-ssl-nginx.conf" > "$data_path/conf/options-ssl-nginx.conf"
  cat "${il_path}/docker-resources/ssl-dhparams.pem" > "$data_path/conf/ssl-dhparams.pem"
  echo
fi


echo "### Creating dummy certificate for $domains ..."
path="/etc/letsencrypt/live/$domains"
mkdir -p "$data_path/conf/live/$domains"
docker compose run --rm --entrypoint "\
    openssl req -x509 -nodes -newkey rsa:$rsa_key_size -days 365\
    -keyout '$path/privkey.pem' \
    -out '$path/fullchain.pem' \
    -subj '/CN=localhost'" certbot
echo

if [ $SELF_SIGNED = 1 ]; then
  echo "### WORKING WITH SELF SIGNED CERTIFICATE"
  docker compose down
  docker compose up -d
  exit 1
fi


echo "### From here generate letsencrypt certificate (if stagging=1 we only try)"


echo "### Starting nginx ..."
docker compose up --force-recreate -d nginx-idelibre
echo


echo "### Deleting dummy certificate for $domains ..."
docker compose run --rm --entrypoint "\
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

docker compose run --rm --entrypoint "\
  certbot certonly --webroot -w /var/www/certbot \
    $staging_arg \
    $email_arg \
    $domain_args \
    --rsa-key-size $rsa_key_size \
    --agree-tos --non-interactive \
    --force-renewal" certbot

echo -e "\n### Reloading nginx ..."
docker compose exec nginx-idelibre nginx -s reload