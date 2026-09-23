# Vite & Gourmand

Application web du traiteur bordelais **Vite & Gourmand** (Julie et José, 25 ans d'expérience).
Elle présente les menus de l'entreprise, permet de les commander en ligne et donne à l'équipe
un espace de gestion complet.

Projet réalisé dans le cadre de l'ECF du titre professionnel **Développeur Web et Web Mobile**.

- **Application en ligne** : https://vite-gourmand-tp-luo4.onrender.com
- **Dépôt** : https://github.com/nabil77130/Vite-Gourmand-TP

> L'hébergement gratuit met le site en veille après 15 minutes sans visite :
> le premier chargement peut alors prendre environ une minute.

---

## Sommaire

1. [Fonctionnalités](#fonctionnalités)
2. [Stack technique](#stack-technique)
3. [Installation en local](#installation-en-local)
4. [Comptes de test](#comptes-de-test)
5. [Base de données](#base-de-données)
6. [Déploiement](#déploiement)
7. [Organisation du dépôt et branches Git](#organisation-du-dépôt-et-branches-git)
8. [Documentation](#documentation)

---

## Fonctionnalités

### Visiteur
- Page d'accueil : présentation de l'entreprise et de l'équipe, avis clients validés.
- Carte des menus avec **filtres dynamiques sans rechargement** : prix maximum, fourchette de prix,
  thème, régime alimentaire, nombre minimum de personnes.
- Fiche détaillée d'un menu : galerie de photos, plats et allergènes, thème, régime,
  conditions du menu mises en évidence, stock disponible.
- Création de compte (mot de passe fort : 10 caractères, majuscule, minuscule, chiffre,
  caractère spécial) avec email de bienvenue.
- Réinitialisation du mot de passe par lien envoyé par email.
- Formulaire de contact transmis par email à l'entreprise.
- Horaires du lundi au dimanche, mentions légales et CGV en pied de page.

### Client (rôle `ROLE_USER`)
- Commande d'un menu : informations pré-remplies, prix mis à jour en direct.
  - Prix = prix par personne × nombre de convives (minimum imposé par le menu).
  - Réduction de 10 % à partir de 5 convives de plus que le minimum.
  - Livraison hors Bordeaux : 5 € + 0,59 € par kilomètre.
- Email de confirmation de commande.
- Espace personnel : commandes, modification et annulation tant que la commande n'est pas acceptée,
  suivi horodaté de tous les statuts, avis (note de 1 à 5 et commentaire) une fois la commande terminée.
- Modification des informations personnelles.

### Employé (rôle `ROLE_EMPLOYEE`)
- Gestion des menus (dont la galerie de photos), des plats et des horaires.
- Commandes : filtre par statut ou par client, passage par les 6 statuts
  (accepté, en préparation, en cours de livraison, livré, en attente du retour de matériel, terminé),
  annulation avec motif et mode de contact obligatoires.
- Emails automatiques : retour du matériel sous 10 jours ouvrés (600 € de frais sinon),
  invitation à donner son avis.
- Validation ou refus des avis clients.

### Administrateur (rôle `ROLE_ADMIN`)
- Tout ce que fait un employé.
- Création de comptes employés (email de notification, sans le mot de passe) et désactivation d'un compte.
  Aucun compte administrateur ne peut être créé depuis l'application.
- **Tableau de bord alimenté par MongoDB** : nombre de commandes par menu (graphique comparatif)
  et chiffre d'affaires par menu, filtrables par menu et par période.

---

## Stack technique

| Couche | Choix |
|---|---|
| Langage | PHP 8.2 |
| Framework | Symfony 6.4 (LTS) |
| Base relationnelle | SQLite, via Doctrine ORM et migrations |
| Base NoSQL | MongoDB Atlas (bibliothèque `mongodb/mongodb`) : statistiques des commandes |
| Front-end | Twig, CSS, JavaScript, Symfony UX Turbo (filtres sans rechargement), AssetMapper |
| Emails | Symfony Mailer, testés avec Mailtrap |
| Déploiement | Docker (PHP 8.2 + Apache) sur Render |

---

## Installation en local

### Prérequis
- **PHP 8.2** ou plus, avec les extensions `pdo_sqlite`, `intl` et **`mongodb`**
  (sous Windows : télécharger la DLL correspondant à sa version de PHP sur PECL,
  la placer dans `ext/` et ajouter `extension=mongodb` dans `php.ini`).
- **Composer** 2.
- **Git**.
- Une base **MongoDB** : un cluster gratuit MongoDB Atlas, ou MongoDB installé en local.
- Pour recevoir les emails de test : un compte **Mailtrap** (facultatif).

### Étapes

1. **Cloner le projet**
   ```bash
   git clone https://github.com/nabil77130/Vite-Gourmand-TP.git
   cd Vite-Gourmand-TP
   ```

2. **Installer les dépendances**
   ```bash
   composer install
   ```

3. **Configurer les accès** : créer un fichier `.env.local` à la racine du projet.
   Ce fichier n'est jamais envoyé sur Git : il contient les identifiants.
   ```dotenv
   # Emails : identifiants SMTP de la boîte de test Mailtrap
   # (ou null://null pour désactiver l'envoi)
   MAILER_DSN="smtp://UTILISATEUR:MOT_DE_PASSE@sandbox.smtp.mailtrap.io:2525"

   # MongoDB : chaîne de connexion Atlas (ou mongodb://localhost:27017)
   MONGODB_URI="mongodb+srv://UTILISATEUR:MOT_DE_PASSE@cluster.xxxxx.mongodb.net/"
   MONGODB_DB="vite_gourmand"
   ```
   Avec MongoDB Atlas, autoriser son adresse IP dans **Network Access**.

4. **Créer la base de données et charger les données de démonstration**
   ```bash
   php bin/console doctrine:migrations:migrate
   php bin/console doctrine:fixtures:load
   ```
   Autre possibilité, avec les fichiers SQL livrés (voir [Base de données](#base-de-données)) :
   ```bash
   sqlite3 var/data.db < sql/schema.sql
   sqlite3 var/data.db < sql/data.sql
   ```

5. **Remplir la base MongoDB** à partir des commandes enregistrées
   ```bash
   php bin/console app:sync-order-stats --purge
   ```

6. **Compiler les fichiers CSS et JavaScript**
   ```bash
   php bin/console asset-map:compile
   ```
   À relancer après chaque modification dans le dossier `assets/`.

7. **Lancer le serveur**
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```
   Puis ouvrir http://127.0.0.1:8000.

---

## Comptes de test

Mot de passe identique pour les trois comptes : `mdp123456789`

| Rôle | Email |
|---|---|
| Administrateur (Julie) | `admin@vite-gourmand.com` |
| Employé (Marc) | `employee@vite-gourmand.com` |
| Client (Jean Dupont) | `user@vite-gourmand.com` |

Ces mots de passe simples sont réservés à la démonstration. Tout compte créé depuis l'application
doit respecter la politique de mot de passe fort.

---

## Base de données

- **Relationnelle (SQLite)** : utilisateurs, menus, plats, allergènes, thèmes, régimes, galerie,
  commandes, historique des statuts, avis, horaires.
  - `sql/schema.sql` : création de toutes les tables, index et clés étrangères.
  - `sql/data.sql` : jeu de données de démonstration (comptes de test, menus, commandes, avis).
- **NoSQL (MongoDB)** : collection `order_stats`, une copie dénormalisée de chaque commande
  (menu, montant, convives, date, statut). Elle alimente le tableau de bord administrateur.
  Chaque commande créée, modifiée ou annulée y est répercutée. En cas d'indisponibilité de MongoDB,
  la commande du client n'échoue pas, et la commande `app:sync-order-stats` permet de resynchroniser.

---

## Déploiement

L'application est déployée sur **Render** à partir de ce dépôt, grâce au `Dockerfile`.

1. **Image Docker** : PHP 8.2 + Apache, extensions `intl`, `zip`, `opcache` et `mongodb`,
   dépendances de production (`composer install --no-dev`), CSS et JavaScript compilés.
2. **Démarrage** (`docker/start.sh`) :
   - création de la base SQLite à partir de `sql/schema.sql` et `sql/data.sql` ;
   - vérification de la configuration des emails et de MongoDB (`docker/diagnostic.php`,
     identifiants masqués dans les journaux) ;
   - synchronisation des statistiques MongoDB, puis lancement d'Apache sur le port fourni par Render.
3. **Variables d'environnement**, saisies dans le tableau de bord Render (jamais dans le code) :
   `APP_ENV=prod`, `APP_SECRET` (générée par Render), `MAILER_DSN` (port 2525),
   `MONGODB_URI`, `MONGODB_DB`.
4. **MongoDB Atlas** : l'accès réseau `0.0.0.0/0` est autorisé, car l'adresse IP de Render
   n'est pas fixe ; la base reste protégée par identifiant et mot de passe.
5. **Mise à jour** : chaque `git push` sur la branche `main` redéploie automatiquement le site.

L'offre gratuite de Render n'a pas de disque persistant : la base SQLite repart du jeu de
démonstration à chaque redémarrage. Ce choix est adapté à une démonstration ; en production,
on utiliserait une base PostgreSQL ou MySQL hébergée.

---

## Organisation du dépôt et branches Git

```
assets/        CSS et JavaScript (AssetMapper)
config/        configuration Symfony
docker/        fichiers de déploiement (Apache, PHP, script de démarrage)
docs/          livrables PDF (charte graphique, manuels, documentation)
migrations/    migrations Doctrine
public/        point d'entrée web et images
sql/           scripts SQL de création et de données
src/           code PHP (contrôleurs, entités, formulaires, services, sécurité)
templates/     vues Twig
```

Branches :
- `main` : version stable, déployée en production.
- `develop` : branche d'intégration, testée avant d'être fusionnée dans `main`.
- `feature/...` : une branche par fonctionnalité, créée depuis `develop`
  et fusionnée dans `develop` après test.

---

## Documentation

Dans le dossier `docs/` :
- charte graphique (palette, polices, maquettes ordinateur et mobile) ;
- manuel d'utilisation, avec les identifiants pour chaque parcours ;
- documentation technique (choix techniques, environnement, modèle de données, diagrammes,
  déploiement).
