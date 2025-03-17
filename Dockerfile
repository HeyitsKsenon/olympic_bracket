# Используем официальный образ PHP
FROM php:8.0-cli

# Устанавливаем необходимые зависимости
RUN apt-get update && apt-get install -y libpng-dev zlib1g-dev && docker-php-ext-install gd && docker-php-ext-install pdo pdo_mysql
RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN echo "zend_extension=xdebug" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log=/var/log/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Копируем файл generate_bracket.php в контейнер
COPY generate_bracket.php /usr/src/myapp/

# Устанавливаем рабочую директорию
WORKDIR /usr/src/myapp

# Команда по умолчанию
CMD [ "php", "./generate_bracket.php" ]