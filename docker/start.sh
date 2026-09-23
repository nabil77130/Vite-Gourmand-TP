#!/bin/sh
# =====================================================================
# Demarrage du conteneur sur Render.
#
# Le disque de l'offre gratuite de Render est efface a chaque redemarrage :
# la base SQLite est donc recreee a chaque demarrage a partir des fichiers
# livrables sql/schema.sql et sql/data.sql. Le site repart ainsi toujours
# du jeu de donnees de demonstration, avec les comptes de test.
# =====================================================================
set -e
cd /var/www/html

echo "[demarrage] Creation de la base SQLite a partir de sql/schema.sql et sql/data.sql"
rm -f var/data.db
sqlite3 var/data.db < sql/schema.sql
sqlite3 var/data.db < sql/data.sql

# Securite : applique une eventuelle migration plus recente que les fichiers SQL.
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Statistiques NoSQL du tableau de bord, recalculees depuis la base SQL.
# Un echec (MongoDB injoignable) ne doit pas empecher le site de demarrer.
php bin/console app:sync-order-stats --purge \
  || echo "[demarrage] ATTENTION : synchronisation MongoDB impossible, le tableau de bord sera vide."

php bin/console cache:warmup
chown -R www-data:www-data var public/uploads

# Render indique le port a utiliser dans la variable PORT.
PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

echo "[demarrage] Site pret sur le port ${PORT}"
exec apache2-foreground
