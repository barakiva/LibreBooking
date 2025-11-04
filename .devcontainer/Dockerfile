FROM mcr.microsoft.com/devcontainers/php:1-8.2-bookworm

RUN apt-get update && \
    apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev build-essential autoconf pkg-config && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd && \
    rm -rf /var/lib/apt/lists/*
