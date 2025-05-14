FROM php:8.2-apache

# Copiar todo el código al directorio raíz de Apache
COPY . /var/www/html/

# Habilitar mod_rewrite si lo usas
RUN a2enmod rewrite

# Dar permisos (opcional, si ves errores de permisos)
RUN chown -R www-data:www-data /var/www/html
