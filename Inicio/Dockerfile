# Use a imagem oficial do PHP com Apache
FROM php:8.2-apache

# Definir informações do mantenedor
LABEL maintainer="Gustavo Schenkel <your-email@domain.com>"
LABEL description="Anthems - Plataforma de Música Social"

# Instalar dependências do sistema
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

# Instalar extensões PHP necessárias
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

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar Apache
RUN a2enmod rewrite
RUN a2enmod ssl
RUN a2enmod headers

# Copiar configuração personalizada do Apache
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache/apache2.conf /etc/apache2/apache2.conf

# Copiar configuração personalizada do PHP
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Definir diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos do projeto
COPY . .

# Criar diretórios necessários e definir permissões
RUN mkdir -p /var/www/html/uploads \
    && mkdir -p /var/www/html/uploads/profiles \
    && mkdir -p /var/www/html/uploads/albums \
    && mkdir -p /var/www/html/logs \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads \
    && chmod -R 777 /var/www/html/logs

# Criar .env a partir do .env.example se não existir
RUN if [ -f /var/www/html/.env.example ] && [ ! -f /var/www/html/.env ]; then \
        cp /var/www/html/.env.example /var/www/html/.env && \
        echo ".env criado a partir de .env.example"; \
    fi

# Definir variáveis de ambiente
ENV APACHE_DOCUMENT_ROOT=/var/www/html
ENV PHP_MEMORY_LIMIT=256M
ENV PHP_UPLOAD_MAX_FILESIZE=10M
ENV PHP_POST_MAX_SIZE=10M

# Expor porta 80
EXPOSE 80

# Comando de inicialização
CMD ["apache2-foreground"]
