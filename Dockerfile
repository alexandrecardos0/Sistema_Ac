FROM php:8.3-fpm

# Instalar dependências do sistema para o Node.js
RUN apt-get update && apt-get install -y curl

# Instalar Node.js e npm
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash -
RUN apt-get install -y nodejs

# Instalar a extensão pdo_mysql
RUN docker-php-ext-install pdo_mysql