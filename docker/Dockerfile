FROM php:8.4-fpm

# 1. Instalar dependências (Tudo como ROOT)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libonig-dev \
    && docker-php-ext-install pdo_pgsql pgsql zip bcmath \
    && rm -rf /var/lib/apt/lists/*

# 2. Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Configurar diretório de trabalho
WORKDIR /var/www

# 4. Criar usuário de desenvolvimento e ajustar permissões de uma vez
RUN useradd -ms /bin/bash -u 1337 devuser && \
    chown -R www-data:www-data /var/www && \
    chmod -R 775 /var/www

# 5. Mudar para o usuário que vai rodar os comandos (opcional para PHP-FPM, mas bom para Artisan)
USER devuser

CMD ["php-fpm"]