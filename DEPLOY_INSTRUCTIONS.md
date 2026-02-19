# 🚀 Guide de Déploiement - Vite! Gourmand

Ce guide explique comment lancer l'application **Vite! Gourmand** dans différents environnements.

## Option 1 : Déploiement Cloud (Azure) - Recommandé

Cette méthode permet d'obtenir une URL publique accessible depuis n'importe où.

1.  Connectez-vous au [Portail Azure](https://portal.azure.com/).
2.  Ouvrez le **Cloud Shell** (icône terminal en haut à droite).
3.  Sélectionnez **Bash**.
4.  Copiez et collez le script ci-dessous :

```bash
# Télécharger et exécuter le script de déploiement automatique
wget https://raw.githubusercontent.com/nabil77130/Vite-Gourmand-TP/main/deploy_azure.sh -O deploy.sh && bash deploy.sh
```

Le script va automatiquement :
-   Créer les ressources Azure.
-   Installer Apache, PHP, SQLite.
-   Cloner le projet et configurer l'application.
-   Vous afficher l'URL finale (ex: `http://vite-gourmand-xxxx.francecentral.cloudapp.azure.com`).

## Option 2 : Lancement Local (Pour test rapide)

Si vous disposez de l'archive du projet :

1.  Assurez-vous d'avoir **PHP 8.2+** et **Composer** installés.
2.  Dans le dossier du projet, lancez :

```bash
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
php -S 127.0.0.1:8000 -t public
```

---
**Note** : L'accès administrateur par défaut est `admin@vite-gourmand.com` / `password`.
