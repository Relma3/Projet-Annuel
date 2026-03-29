FROM php:8.2-apache

# Dépendances système + extensions PHP
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libzip-dev \
    libonig-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_mysql mysqli curl zip mbstring

# Activer mod_rewrite
RUN a2enmod rewrite

# Copier le code
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html

# Config Apache AllowOverride
RUN echo '<Directory /var/www/html>\nAllowOverride All\nRequire all granted\n</Directory>' \
    > /etc/apache2/conf-available/silverhappy.conf \
    && a2enconf silverhappy

EXPOSE 80