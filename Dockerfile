# Silver Happy — Dockerfile service web PHP + Apache
# Build multi-étapes : dev et prod séparés

# ── Stage développement ───────────────────────────────────────
FROM php:8.2-apache AS dev

RUN apt-get update && apt-get install -y curl unzip \
    && docker-php-ext-install mysqli pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer pour les dépendances PHP (Stripe, DomPDF...)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Module Apache rewrite pour les routes .htaccess
RUN a2enmod rewrite
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

WORKDIR /var/www/html
# En dev le code est monté en volume — pas de COPY ici

# ── Stage production ──────────────────────────────────────────
FROM php:8.2-apache AS prod

RUN apt-get update && apt-get install -y curl unzip \
    && docker-php-ext-install mysqli pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# En prod on copie tout le code dans l'image
COPY . .

# Installation des dépendances sans outils de dev
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader --no-interaction; fi

# Créer les dossiers storage avec les bonnes permissions
RUN mkdir -p storage/factures storage/logs \
    && chown -R www-data:www-data storage \
    && chmod -R 755 storage

# Nettoyage des fichiers inutiles en prod
RUN rm -rf .git tests *.md docker-compose*.yml Dockerfile*

EXPOSE 80
