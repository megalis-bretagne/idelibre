FROM ubuntu:22.04 as idelibrefpm

ARG TIMEZONE=UTC
RUN ln -snf /usr/share/zoneinfo/$TIMEZONE /etc/localtime && echo $TIMEZONE > /etc/timezone

ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="0" \
    PHP_OPCACHE_MAX_ACCELERATED_FILES="10000" \
    PHP_OPCACHE_MEMORY_CONSUMPTION="192" \
    PHP_OPCACHE_MAX_WASTED_PERCENTAGE="10" \
    PHP_MAX_FILE_UPLOADS='600' \
    PHP_UPLOAD_MAX_FILESIZE='200M' \
    PHP_POST_MAX_SIZE='2000M' \
    PHP_MEMORY_LIMIT='2100M' \
    PHP_MAX_INPUT_VAR='5000'  \
    PHP_MAX_EXECUTION_TIME='300' \
    PHP_PM_MAX_CHILDREN='15' \
    PHP_PM_START_SERVERS='3' \
    PHP_PM_MIN_SPARE_SERVERS='2' \
    PHP_PM_MAX_SPARE_SERVERS='4'

RUN apt-get update -yqq \
    && apt dist-upgrade -yqq \
    && apt-get install \
        wget \
        sudo \
        vim \
        curl \
        git \
        zip \
        unzip \
        locales \
        netcat \
        openssl \
        xfonts-75dpi \
        fontconfig \
        xfonts-base \
        poppler-utils \
        qpdf \
        -yqq

RUN apt install php-fpm php-intl php-mbstring php-xml php-zip php-pgsql php-curl php-pcov -y

COPY docker-resources/wkhtmltox_0.12.6.1-2.jammy_amd64.deb /tmp/wkhtmltox.deb

RUN apt install /tmp/wkhtmltox.deb -y

RUN apt install gnupg -yy
RUN sh -c 'echo "deb https://apt.postgresql.org/pub/repos/apt bullseye-pgdg main" > /etc/apt/sources.list.d/pgdg.list' && \
wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add - && \
apt-get update -yy


RUN apt-get install postgresql-client-12 -yy


RUN curl -sL https://deb.nodesource.com/setup_18.x | sudo bash -
RUN apt-get install nodejs -yqq

COPY --chown=www-data:www-data . /app

WORKDIR /app

RUN chmod +x docker-resources/initApp.sh

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN cd /app && \
    composer install && \
    npm install && \
    npm run build


RUN mkdir -p /data /data/workspace /data/pdf /data/token /data/zip
RUN chown -R www-data:www-data /data


RUN sed -i "s|/run/php/php8.1-fpm.sock|9000|g" /etc/php/8.1/fpm/pool.d/www.conf
RUN sed -i "s|;clear_env = no|clear_env = no|g" /etc/php/8.1/fpm/pool.d/www.conf

COPY docker-resources/opcache.ini /etc/php/8.1/fpm/conf.d/opcache.ini
COPY docker-resources/zz-idelibre.ini /etc/php/8.1/fpm/conf.d/zz-idelibre.ini

EXPOSE 9000
