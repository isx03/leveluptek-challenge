# Usar la imagen oficial de PHP con Apache
FROM php:8.4-apache

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install zip pdo pdo_pgsql

# Habilitar el módulo de Apache rewrite
RUN a2enmod rewrite

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Copiar el archivo composer.json y composer.lock
COPY composer.json composer.lock ./

# Instalar dependencias de Composer
RUN composer install --no-scripts --no-autoloader

# Copiar el resto de la aplicación
COPY . .

# Generar el autoloader de Composer
RUN composer dump-autoload --optimize

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer el puerto 80
EXPOSE 80

# Comando para iniciar Apache
CMD ["apache2-foreground"]