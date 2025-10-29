FROM php:8.2-apache

# Install PHP extensions required by LibreBooking
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite for URL rewriting
RUN a2enmod rewrite

# Install Composer (latest official PHAR)
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && rm composer-setup.php

# Set working directory
WORKDIR /var/www/html
