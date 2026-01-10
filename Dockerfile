# Dockerfile (na raiz do projeto)

# Usando imagem oficial PHP com Apache
FROM php:8.2-apache

# ==================== INSTALAÇÕES E CONFIGURAÇÕES BÁSICAS ====================

# Atualiza pacotes e instala dependências comuns
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        zip \
        gd \
        mbstring \
        xml \
        soap \
    && a2enmod rewrite

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ==================== CONFIGURAÇÃO DO PROJETO ====================

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia todo o projeto (exceto arquivos desnecessários - veja .dockerignore)
COPY . /var/www/html/

# Permissões corretas (importante para Laravel/Symfony ou projetos PHP comuns)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Porta exposta (Apache padrão)
EXPOSE 80

# Comando padrão (já vem na imagem php:apache)
# CMD ["apache2-foreground"]
