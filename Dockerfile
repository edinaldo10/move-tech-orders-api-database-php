FROM php:8.2-apache

# Instala dependências do sistema e extensões do PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Habilita o mod_rewrite do Apache
RUN a2enmod rewrite

# Libera o AllowOverride para o .htaccess funcionar na pasta public
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/public-dir.conf \
    && a2enconf public-dir

# Altera o DocumentRoot padrão do Apache para a pasta public/
RUN sed -i 's!/var/www/html/public!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Copia o Composer oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia primeiro os arquivos de dependência para aproveitar o cache
COPY composer.json composer.lock ./

# Instala as dependências do PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copia o restante do código fonte para dentro do container
COPY . .

# Ajusta permissões finais
RUN chown -R www-data:www-data /var/www/html
# Força o Apache a escutar na porta 8080
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf
RUN sed -i 's/:80/:8080/g' /etc/apache2/sites-available/000-default.conf