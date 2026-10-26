# syntax=docker/dockerfile:1

# ---------- Étape 1 : dépendances PHP (sans les outils de dev) ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction \
        --no-scripts --optimize-autoloader

# ---------- Étape 2 : image finale (Apache + PHP 8.2) ----------
FROM php:8.2-apache-bookworm

# PORT : Render fournit sa propre valeur au démarrage (10000 par défaut).
# Apache lit ${PORT} dans sa config, donc le port suit toujours la variable.
ENV PORT=10000

RUN apt-get update \
    && apt-get install -y --no-install-recommends default-mysql-client \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql opcache \
    && a2enmod headers \
    && sed -ri 's/^Listen 80$/Listen ${PORT}/' /etc/apache2/ports.conf \
    && sed -ri 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/' /etc/apache2/sites-available/000-default.conf

# Configuration (hors de la racine web)
COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-school.ini"
COPY docker/apache.conf /etc/apache2/conf-available/school.conf
COPY docker/entrypoint.sh /usr/local/bin/school-entrypoint.sh
COPY docker/db/schema.sql /opt/school/schema.sql
RUN a2enconf school \
    && sed -i 's/\r$//' /usr/local/bin/school-entrypoint.sh \
    && chmod +x /usr/local/bin/school-entrypoint.sh

# Code de l'application (le .dockerignore écarte .git, vendor, .env, *.sql...)
WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
RUN rm -rf docker Dockerfile docker-compose.yml render.yaml

EXPOSE 10000
ENTRYPOINT ["/usr/local/bin/school-entrypoint.sh"]
CMD ["apache2-foreground"]
