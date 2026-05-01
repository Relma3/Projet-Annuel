FROM webdevops/php-apache:8.2

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader --no-interaction; fi

RUN mkdir -p storage/factures storage/logs \
    && chown -R www-data:www-data storage \
    && chmod -R 755 storage

RUN a2enmod ssl rewrite
COPY apache-ssl.conf /opt/docker/etc/httpd/vhost.conf

EXPOSE 80 443