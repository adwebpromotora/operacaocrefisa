# Usando imagem oficial PHP com FPM + Nginx
FROM php:8.2-fpm-alpine

# Instalar dependências do sistema e extensões PHP necessárias
RUN apk update && apk add --no-cache \
    nginx \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    oniguruma-dev \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
    && apk del --no-cache $PHPIZE_DEPS

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /var/www

# Copiar todos os arquivos do projeto
COPY . .

# Ajustar permissões
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage 2>/dev/null || true

# Copiar configuração do Nginx
COPY nginx.conf /etc/nginx/http.d/default.conf

# Expor a porta 80 (Nginx)
EXPOSE 80

# Iniciar PHP-FPM e Nginx juntos
CMD php-fpm & nginx -g "daemon off;"
