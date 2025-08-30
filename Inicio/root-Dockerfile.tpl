# Root-level Dockerfile for Anthems (expects app code in ./Inicio)
FROM php:8.2-apache

LABEL maintainer="Gustavo Schenkel <your-email@domain.com>"
LABEL description="Anthems - Plataforma de Música Social"

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite && a2enmod ssl && a2enmod headers

# Apache/PHP configs from docker/ at repo root
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache/apache2.conf /etc/apache2/apache2.conf
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

# Copy app code from subfolder Inicio
COPY Inicio/ .

RUN mkdir -p /var/www/html/uploads \
    && mkdir -p /var/www/html/uploads/profiles \
    && mkdir -p /var/www/html/uploads/albums \
    && mkdir -p /var/www/html/logs \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads \
    && chmod -R 777 /var/www/html/logs

ENV APACHE_DOCUMENT_ROOT=/var/www/html
ENV PHP_MEMORY_LIMIT=256M
ENV PHP_UPLOAD_MAX_FILESIZE=10M
ENV PHP_POST_MAX_SIZE=10M

EXPOSE 80
CMD ["apache2-foreground"]
