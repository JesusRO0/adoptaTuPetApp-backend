# Usa una imagen de PHP con Apache
FROM php:8.1-apache

# Instala el driver de MySQL para PDO
RUN docker-php-ext-install pdo pdo_mysql

# Copia todo el contenido del proyecto al contenedor
COPY . /var/www/html/

# Activa el módulo rewrite de Apache (opcional)
RUN a2enmod rewrite

# Establece permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expone el puerto 80
EXPOSE 80
