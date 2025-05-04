FROM php:8.2-cli

WORKDIR /var/www/code/php

RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    libzip-dev \
    git && \
    docker-php-ext-install zip sockets && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

COPY code-share/php/composer.json ./
RUN composer install

COPY code-share/php/ ./
COPY code-share/php/php.ini /usr/local/etc/php/php.ini

CMD ["php", "/var/www/code/php/index.php"]
