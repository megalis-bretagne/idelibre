FROM php:7.4-fpm

RUN mkdir -p /usr/share/man/man1/ /usr/share/man/man3/ /usr/share/man/man7/

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
        zlib1g-dev \
        libxml2-dev \
        libicu-dev \
        libpq-dev \
        libonig-dev \
        libzip-dev \
        netcat \
        openssl \
        pdftk \
        xfonts-75dpi \
        fontconfig \
        xfonts-base \
        -yqq


RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl mbstring xml zip pdo pdo_pgsql pgsql opcache


COPY ./docker-resources/wkhtmltox_0.12.6-1.buster_amd64.deb /tmp/wkhtmltox.deb
RUN  dpkg -i /tmp/wkhtmltox.deb


RUN pecl install -o -f redis \
  &&  rm -rf /tmp/pear \
  &&  docker-php-ext-enable redis

COPY . /app
WORKDIR /app

RUN curl -s https://getcomposer.org/installer | php && php composer.phar install --no-interaction
RUN chown -R www-data:www-data /app

RUN mkdir -p /data
RUN chown -R www-data:www-data /data


COPY ./docker-resources/zz-idelibre.conf /usr/local/etc/php-fpm.d/zz-idelibre.conf

COPY docker-resources/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker-resources/zz-php.ini /usr/local/etc/php/conf.d/zz-php.ini


EXPOSE 9000
CMD ["php-fpm"]
