# PHP-FPM base image
FROM php:8.2-fpm

# Install required PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install Composer (latest official PHAR)
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && rm composer-setup.php

WORKDIR /var/www/html
