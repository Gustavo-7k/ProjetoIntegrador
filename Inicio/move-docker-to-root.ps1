$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

function Backup-IfExists {
  param(
    [Parameter(Mandatory)][string]$Path
  )
  if (Test-Path $Path) {
    $bak = "$Path.bak_$(Get-Date -Format yyyyMMdd_HHmmss)"
    Copy-Item -Path $Path -Destination $bak -Force -ErrorAction SilentlyContinue | Out-Null
  }
}

$src = $PSScriptRoot            # ...\projetoinsano\Inicio
$dst = Split-Path -Parent $src   # ...\projetoinsano

Write-Host "[INFO] Origem: $src" -ForegroundColor Cyan
Write-Host "[INFO] Destino: $dst" -ForegroundColor Cyan

# 1) Pastas docker/ no root
$dockerRoot = Join-Path $dst 'docker'
$null = New-Item -ItemType Directory -Path (Join-Path $dockerRoot 'apache') -Force
$null = New-Item -ItemType Directory -Path (Join-Path $dockerRoot 'php') -Force
$null = New-Item -ItemType Directory -Path (Join-Path $dockerRoot 'mysql') -Force

Copy-Item -Path (Join-Path $src 'docker/apache/*.conf') -Destination (Join-Path $dockerRoot 'apache') -Force -ErrorAction SilentlyContinue
Copy-Item -Path (Join-Path $src 'docker/php/*.ini')    -Destination (Join-Path $dockerRoot 'php')    -Force -ErrorAction SilentlyContinue
Copy-Item -Path (Join-Path $src 'docker/mysql/my.cnf') -Destination (Join-Path $dockerRoot 'mysql')  -Force -ErrorAction SilentlyContinue

# 2) Gerar Dockerfile no root (apontando para ./Inicio)
$dockerfileRoot = @'
# Use a imagem oficial do PHP com Apache
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

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache/apache2.conf     /etc/apache2/apache2.conf
COPY docker/php/php.ini             /usr/local/etc/php/php.ini
COPY docker/php/opcache.ini         /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

# Copiar o código do app a partir de ./Inicio
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
'@

Backup-IfExists (Join-Path $dst 'Dockerfile')
Set-Content -Path (Join-Path $dst 'Dockerfile') -Value $dockerfileRoot -Encoding UTF8

# 3) Gerar docker-compose.yml no root (montando ./Inicio)
$composeRoot = @'
services:
  anthems-web:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: anthems-web
    ports:
      - "8080:80"
    environment:
      APP_URL: ${APP_URL:-http://localhost:8080}
      PHP_MEMORY_LIMIT: ${PHP_MEMORY_LIMIT:-256M}
      PHP_POST_MAX_SIZE: ${PHP_POST_MAX_SIZE:-64M}
      PHP_UPLOAD_MAX_FILESIZE: ${PHP_UPLOAD_MAX_FILESIZE:-64M}
      DB_HOST: anthems-db
      DB_DATABASE: ${MYSQL_DATABASE:-anthems_db}
      DB_USERNAME: ${MYSQL_USER:-anthems_user}
      DB_PASSWORD: ${MYSQL_PASSWORD}
    volumes:
      - ./Inicio:/var/www/html
    depends_on:
      - anthems-db
      - anthems-redis
    restart: unless-stopped

  anthems-db:
    image: mysql:8.0
    container_name: anthems-db
    command: --default-authentication-plugin=mysql_native_password
    ports:
      - "3306:3306"
    environment:
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MYSQL_DATABASE: ${MYSQL_DATABASE:-anthems_db}
      MYSQL_USER: ${MYSQL_USER}
      MYSQL_PASSWORD: ${MYSQL_PASSWORD}
    volumes:
      - db_data:/var/lib/mysql
      - ./Inicio/database.sql:/docker-entrypoint-initdb.d/01_database.sql:ro
      - ./docker/mysql/my.cnf:/etc/mysql/conf.d/my.cnf:ro
    restart: unless-stopped

  anthems-phpmyadmin:
    image: phpmyadmin:5
    container_name: anthems-phpmyadmin
    environment:
      PMA_HOST: anthems-db
      PMA_PORT: 3306
      PMA_ABSOLUTE_URI: http://localhost:8081/
    ports:
      - "8081:80"
    depends_on:
      - anthems-db
    restart: unless-stopped

  anthems-redis:
    image: redis:7-alpine
    container_name: anthems-redis
    command: ["redis-server", "--save", "60", "1", "--loglevel", "warning"]
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    restart: unless-stopped

volumes:
  db_data:
  redis_data:
'@

Backup-IfExists (Join-Path $dst 'docker-compose.yml')
Set-Content -Path (Join-Path $dst 'docker-compose.yml') -Value $composeRoot -Encoding UTF8

# 4) .env e .env.example para o root (se não existirem)
if (-not (Test-Path (Join-Path $dst '.env')) -and (Test-Path (Join-Path $src '.env'))) {
  Copy-Item -Path (Join-Path $src '.env') -Destination (Join-Path $dst '.env') -Force
}
if (-not (Test-Path (Join-Path $dst '.env.example')) -and (Test-Path (Join-Path $src '.env.example'))) {
  Copy-Item -Path (Join-Path $src '.env.example') -Destination (Join-Path $dst '.env.example') -Force
}

# 5) Scripts utilitários para o root
$scripts = @('docker-manager.bat','docker-quickstart.bat','docker-quickstart.ps1')
foreach ($s in $scripts) {
  $srcFile = Join-Path $src $s
  if (Test-Path $srcFile) {
    Copy-Item -Path $srcFile -Destination (Join-Path $dst $s) -Force
  }
}

Write-Host "[OK] Arquivos Docker movidos/gerados no root do projeto." -ForegroundColor Green
Write-Host "[INFO] Agora execute os scripts a partir de: $dst" -ForegroundColor Cyan
Write-Host "      Ex.: docker-quickstart.bat ou docker-manager.bat" -ForegroundColor Cyan

exit 0
