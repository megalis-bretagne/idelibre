FROM ubuntu:22.04 as idelibrefpm

ARG TIMEZONE=UTC
RUN ln -snf /usr/share/zoneinfo/$TIMEZONE /etc/localtime && echo $TIMEZONE > /etc/timezone

ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="0" \
    PHP_OPCACHE_MAX_ACCELERATED_FILES="10000" \
    PHP_OPCACHE_MEMORY_CONSUMPTION="192" \
    PHP_OPCACHE_MAX_WASTED_PERCENTAGE="10"

RUN apt-get update -yqq \
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

RUN apt install php-fpm php-intl php-mbstring php-xml php-zip php-pgsql  -y

RUN apt install wkhtmltopdf -y

RUN apt install gnupg -yy
RUN sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt bullseye-pgdg main" > /etc/apt/sources.list.d/pgdg.list' && \
wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add - && \
apt-get update -yy

RUN apt-get install postgresql-client-12 -yy

RUN curl -sL https://deb.nodesource.com/setup_16.x | sudo bash -
RUN apt-get install nodejs -yqq

COPY --chown=www-data:www-data . /app

WORKDIR /app

#RUN curl -s https://getcomposer.org/installer | php && sudo -u www-data php composer.phar install --no-interaction --no-cache
#RUN npm install && npm run build

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN cd /app && \
    composer install --no-scripts --no-dev --no-cache && \
    npm install && \
    npm run build

RUN mkdir -p /data
RUN chown -R www-data:www-data /data

RUN sed -i "s|/run/php/php8.1-fpm.sock|9000|g" /etc/php/8.1/fpm/pool.d/www.conf
RUN sed -i "s|;clear_env = no|clear_env = no|g" /etc/php/8.1/fpm/pool.d/www.conf

COPY docker-resources/opcache.ini /etc/php/8.1/fpm/conf.d/opcache.ini
COPY docker-resources/zz-php.ini /etc/php/8.1/fpm/conf.d/zz-php.ini
COPY docker-resources/zz-idelibre.conf /etc/php/8.1/fpm/conf.d/zz-idelibre.conf

#RUN echo  "0 2 * * * /app/bin/console purge:structure" > /etc/cron.d/idelibre_purge_daily

EXPOSE 9000
#ENTRYPOINT php-fpm8.1 --nodaemonize"

#CMD "php-fpm8.1 -F"