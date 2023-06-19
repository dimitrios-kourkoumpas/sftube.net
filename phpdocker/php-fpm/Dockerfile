FROM phpdockerio/php:8.2-fpm
WORKDIR "/application"

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update; \
    apt-get -y --no-install-recommends install \
        git \
        php8.2-amqp \
        php8.2-bcmath \
        php8.2-bz2 \
        php8.2-curl \
        php8.2-gd \
        php8.2-gmp \
        php8.2-imagick \
        php8.2-intl \
        php8.2-mbstring \
        php8.2-mcrypt \
        php8.2-mysql \
        php8.2-oauth \
        php8.2-raphf \
        php8.2-redis \
        php8.2-xml \
        php8.2-yaml;

## Install ffmpeg
RUN apt-get update && apt-get -y --no-install-recommends install ffmpeg

## Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*
