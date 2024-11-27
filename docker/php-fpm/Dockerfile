FROM php:8.3-fpm-alpine

# Установить системные зависимости
RUN apk add --no-cache \
    postgresql-dev \
    git \
    zip \
    unzip \
    rabbitmq-c-dev \
    linux-headers \
    $PHPIZE_DEPS

# Установить PHP-расширения
RUN docker-php-ext-install pdo_pgsql sockets

# Установить PECL-расширения
RUN pecl install xdebug xhprof amqp && \
    docker-php-ext-enable xdebug amqp xhprof

# Установить Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копировать конфигурационные файлы PHP
COPY ./conf.d/php.ini /usr/local/etc/php/php.ini
COPY ./conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Удалить временные файлы
RUN rm -rf /tmp/pear