FROM php:8.1-fpm-alpine

RUN apk --no-cache update \
    && apk --no-cache upgrade \
    && apk add curl unzip

RUN apk add --no-cache libzip-dev zlib-dev libpng-dev oniguruma-dev libcurl curl-dev libxml2-dev

RUN docker-php-ext-install mysqli pdo pdo_mysql exif zip gd mbstring curl xml bcmath tokenizer

# Install Composer
RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
RUN HASH=`curl -sS https://composer.github.io/installer.sig`
RUN php -r "if (hash_file('SHA384', '/tmp/composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer