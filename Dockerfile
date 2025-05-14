# Usa una imagen de PHP con Apache
FROM php:8.1-apache

# Copia TODO el contenido del repositorio a la carpeta donde Apache sirve el contenido
COPY . /var/www/html/

# Activa el módulo rewrite (útil para URLs amigables si lo necesitas)
RUN a2enmod rewrite

# Ajusta permisos (opcional pero recomendado)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expone el puerto 80 (lo detecta Render automáticamente)
EXPOSE 80
