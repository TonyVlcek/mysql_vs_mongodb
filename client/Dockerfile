FROM php:8.1-cli

ENV COMPOSER_ALLOW_SUPERUSER 1

COPY . /usr/src/mymo-client
WORKDIR /usr/src/mymo-client

# Install MongoDB extension
RUN apt-get update && apt-get install -y \
    git zip unzip \
    openssl \
    libssl-dev \
    libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/*
RUN pecl install mongodb && docker-php-ext-enable mongodb
RUN echo "extension=mongodb.so" >> /usr/local/etc/php/php.ini

# Install MySQL PDO extension
RUN docker-php-ext-install pdo pdo_mysql

# Increase memory limit
RUN echo "memory_limit=1024M" > /usr/local/etc/php/conf.d/memory-limit.ini

# Install composer dependencies
RUN curl --silent --show-error https://getcomposer.org/installer | php && \
    php composer.phar install --prefer-dist --no-progress --no-suggest --optimize-autoloader --classmap-authoritative --no-interaction && \
    php composer.phar clear-cache && \
    rm -rf /usr/src/php

# Target must be passed via env variable called NETTE__TARGET, supported values are 'mongo' or 'mysql'
CMD ["php", "./bin/console", "run:all"]
