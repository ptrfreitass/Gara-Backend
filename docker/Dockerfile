FROM php:8.3-fpm

# Instalar dependências do sistema necessárias para extensões e ferramentas
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    && docker-php-ext-install pdo_pgsql pgsql zip mbstring \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer (a partir da imagem oficial do Composer)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Diretório de trabalho
WORKDIR /var/www/html

# Ajustar permissões básicas
RUN chown -R www-data:www-data /var/www

CMD ["php-fpm"]