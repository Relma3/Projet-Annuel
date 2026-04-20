FROM php:8.2-apache AS dev

RUN apt-get update && apt-get install -y curl unzip \
    && docker-php-ext-install mysqli pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

FROM php:8.2-apache AS prod

RUN apt-get update && apt-get install -y curl unzip \
    && docker-php-ext-install mysqli pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

COPY . .

RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader --no-interaction; fi

RUN mkdir -p storage/factures storage/logs \
    && chown -R www-data:www-data storage \
    && chmod -R 755 storage

RUN rm -rf .git tests *.md docker-compose*.yml Dockerfile*

EXPOSE 80