FROM php:8.3-fpm

# Dependências do sistema e utilitários
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       git curl unzip \
       libzip-dev \
       libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Node.js 20.x e npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get update \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

# Extensões PHP necessárias (pdo_mysql, zip, gd, bcmath)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql zip gd bcmath

# Redis (opcional, caso REDIS_CLIENT=phpredis)
RUN pecl install redis \
    && docker-php-ext-enable redis || true

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dica: o código é montado via volume no docker-compose.
# Se quiser build sem volume, descomente e use COPY.
# COPY . /var/www/html
