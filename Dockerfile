# =====================================================================
# Vite & Gourmand - image de production (hebergement Render)
#
# PHP 8.2 + Apache, avec les extensions dont l'application a besoin,
# dont l'extension MongoDB (base NoSQL du tableau de bord).
# =====================================================================
FROM php:8.2-apache

# Extensions PHP et outils systeme.
#  - intl, zip, opcache : Symfony et Composer
#  - mongodb (PECL)     : connexion a MongoDB Atlas
#  - sqlite3            : creation de la base a partir de sql/*.sql
RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip libicu-dev libzip-dev libssl-dev sqlite3 \
 && docker-php-ext-install intl zip opcache \
 && pecl install mongodb \
 && docker-php-ext-enable mongodb \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache sert le dossier public/ ; toute URL inconnue est confiee a Symfony.
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html
COPY . .

# Dependances de production uniquement, puis CSS/JS compiles dans public/assets.
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress \
 && php bin/console asset-map:compile \
 && mkdir -p var public/uploads/menus \
 && chown -R www-data:www-data var public/uploads

COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
