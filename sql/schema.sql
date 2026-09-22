-- =====================================================================
-- Vite & Gourmand - Creation de la base de donnees
-- SGBD : SQLite 3 (base utilisee par l'application : var/data.db)
-- Genere a partir de la base de l'application le 23/09/2026.
-- =====================================================================

-- Utilisation :
--   sqlite3 var/data.db < sql/schema.sql
--   sqlite3 var/data.db < sql/data.sql
--
-- Le script peut etre relance : les tables existantes sont supprimees
-- avant d'etre recreees.

PRAGMA foreign_keys = OFF;

DROP TABLE IF EXISTS doctrine_migration_versions;
DROP TABLE IF EXISTS messenger_messages;
DROP TABLE IF EXISTS review;
DROP TABLE IF EXISTS order_status_history;
DROP TABLE IF EXISTS order_item;
DROP TABLE IF EXISTS "order";
DROP TABLE IF EXISTS menu_image;
DROP TABLE IF EXISTS menu_diet;
DROP TABLE IF EXISTS menu_theme;
DROP TABLE IF EXISTS menu_product;
DROP TABLE IF EXISTS product_theme;
DROP TABLE IF EXISTS product_diet;
DROP TABLE IF EXISTS product_allergen;
DROP TABLE IF EXISTS menu;
DROP TABLE IF EXISTS product;
DROP TABLE IF EXISTS horaire;
DROP TABLE IF EXISTS theme;
DROP TABLE IF EXISTS diet;
DROP TABLE IF EXISTS allergen;
DROP TABLE IF EXISTS user;

PRAGMA foreign_keys = ON;

-- Comptes : clients, employes et administrateur (roles en JSON).
CREATE TABLE user (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    email VARCHAR(180) NOT NULL,
    roles CLOB NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    address VARCHAR(255) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    zip_code VARCHAR(20) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    pays VARCHAR(100) DEFAULT NULL,
    is_active BOOLEAN DEFAULT 1 NOT NULL,
    reset_token VARCHAR(100) DEFAULT NULL,
    reset_token_expires_at DATETIME DEFAULT NULL
);
CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email);

-- Allergenes (gluten, arachides...).
CREATE TABLE allergen (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    name VARCHAR(255) NOT NULL
);

-- Regimes alimentaires (vegetarien, vegan...).
CREATE TABLE diet (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    name VARCHAR(255) NOT NULL
);

-- Thematiques (italien, asiatique...).
CREATE TABLE theme (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    name VARCHAR(255) NOT NULL
);

-- Horaires d'ouverture affiches en pied de page.
CREATE TABLE horaire (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    jour VARCHAR(20) NOT NULL,
    heure_ouverture VARCHAR(10) DEFAULT NULL,
    heure_fermeture VARCHAR(10) DEFAULT NULL,
    ferme BOOLEAN NOT NULL
);

-- Plats (entree, plat, dessert, boisson).
CREATE TABLE product (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description CLOB DEFAULT NULL,
    price DOUBLE PRECISION NOT NULL,
    image_name VARCHAR(255) DEFAULT NULL,
    is_available BOOLEAN NOT NULL,
    category VARCHAR(20) NOT NULL
);

-- Menus proposes a la commande. stock = nombre de commandes encore possibles (NULL = illimite).
CREATE TABLE menu (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description CLOB DEFAULT NULL,
    price NUMERIC(10, 2) NOT NULL,
    min_people INTEGER DEFAULT NULL,
    stock INTEGER DEFAULT NULL,
    image_name VARCHAR(255) DEFAULT NULL,
    conditions CLOB DEFAULT NULL
);

-- Liaison plat <-> allergene.
CREATE TABLE product_allergen (
    product_id INTEGER NOT NULL,
    allergen_id INTEGER NOT NULL,
    PRIMARY KEY (product_id, allergen_id),
    CONSTRAINT FK_EE0F62594584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
    CONSTRAINT FK_EE0F62596E775A4A FOREIGN KEY (allergen_id) REFERENCES allergen (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
);
CREATE INDEX IDX_EE0F62594584665A ON product_allergen (product_id);
CREATE INDEX IDX_EE0F62596E775A4A ON product_allergen (allergen_id);

-- Liaison plat <-> regime.
CREATE TABLE product_diet (
    product_id INTEGER NOT NULL,
    diet_id INTEGER NOT NULL,
    PRIMARY KEY (product_id, diet_id),
    CONSTRAINT FK_100C47814584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
    CONSTRAINT FK_100C4781E1E13ACE FOREIGN KEY (diet_id) REFERENCES diet (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
);
CREATE INDEX IDX_100C47814584665A ON product_diet (product_id);
CREATE INDEX IDX_100C4781E1E13ACE ON product_diet (diet_id);

-- Liaison plat <-> thematique.
CREATE TABLE product_theme (
    product_id INTEGER NOT NULL,
    theme_id INTEGER NOT NULL,
    PRIMARY KEY (product_id, theme_id),
    CONSTRAINT FK_36299C544584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
    CONSTRAINT FK_36299C5459027487 FOREIGN KEY (theme_id) REFERENCES theme (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
);
CREATE INDEX IDX_36299C544584665A ON product_theme (product_id);
CREATE INDEX IDX_36299C5459027487 ON product_theme (theme_id);

-- Composition d'un menu (liaison menu <-> plat).
CREATE TABLE menu_product (
    menu_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    PRIMARY KEY (menu_id, product_id),
    CONSTRAINT FK_5B911913CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
    CONSTRAINT FK_5B9119134584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
);
CREATE INDEX IDX_5B911913CCD7E912 ON menu_product (menu_id);
CREATE INDEX IDX_5B9119134584665A ON menu_product (product_id);

-- Liaison menu <-> thematique (filtres de la carte).
CREATE TABLE menu_theme (
    menu_id INTEGER NOT NULL,
    theme_id INTEGER NOT NULL,
    PRIMARY KEY (menu_id, theme_id),
    CONSTRAINT FK_6D9C46FCCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
    CONSTRAINT FK_6D9C46F59027487 FOREIGN KEY (theme_id) REFERENCES theme (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
);
CREATE INDEX IDX_6D9C46FCCD7E912 ON menu_theme (menu_id);
CREATE INDEX IDX_6D9C46F59027487 ON menu_theme (theme_id);

-- Liaison menu <-> regime (filtres de la carte).
CREATE TABLE menu_diet (
    menu_id INTEGER NOT NULL,
    diet_id INTEGER NOT NULL,
    PRIMARY KEY (menu_id, diet_id),
    CONSTRAINT FK_55AB956ECCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
    CONSTRAINT FK_55AB956EE1E13ACE FOREIGN KEY (diet_id) REFERENCES diet (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
);
CREATE INDEX IDX_55AB956ECCD7E912 ON menu_diet (menu_id);
CREATE INDEX IDX_55AB956EE1E13ACE ON menu_diet (diet_id);

-- Galerie de photos d'un menu (chemin relatif au dossier public/).
CREATE TABLE menu_image (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    menu_id INTEGER NOT NULL,
    path VARCHAR(255) NOT NULL,
    alt VARCHAR(255) DEFAULT NULL,
    position INTEGER NOT NULL,
    CONSTRAINT FK_54912738CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
);
CREATE INDEX IDX_54912738CCD7E912 ON menu_image (menu_id);

-- Commandes des clients. "order" est un mot reserve SQL, d'ou les guillemets.
CREATE TABLE "order" (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    created_at DATETIME NOT NULL,
    status VARCHAR(20) NOT NULL,
    total_price DOUBLE PRECISION NOT NULL,
    user_id INTEGER NOT NULL,
    delivery_time TIME DEFAULT NULL,
    event_date DATE DEFAULT NULL,
    equipment_loan BOOLEAN DEFAULT NULL,
    equipment_return BOOLEAN DEFAULT NULL,
    nombre_personne INTEGER DEFAULT NULL,
    prix_livraison DOUBLE PRECISION DEFAULT NULL,
    cancellation_reason CLOB DEFAULT NULL,
    adresse_prestation VARCHAR(255) DEFAULT NULL,
    CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
);
CREATE INDEX IDX_F5299398A76ED395 ON "order" (user_id);

-- Ligne de commande : le menu commande.
CREATE TABLE order_item (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    quantity INTEGER NOT NULL,
    price_at_order DOUBLE PRECISION NOT NULL,
    order_ref_id INTEGER NOT NULL,
    product_id INTEGER DEFAULT NULL,
    menu_id INTEGER DEFAULT NULL,
    CONSTRAINT FK_52EA1F09E238517C FOREIGN KEY (order_ref_id) REFERENCES "order" (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE,
    CONSTRAINT FK_52EA1F094584665A FOREIGN KEY (product_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE,
    CONSTRAINT FK_52EA1F09CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) NOT DEFERRABLE INITIALLY IMMEDIATE
);
CREATE INDEX IDX_52EA1F094584665A ON order_item (product_id);
CREATE INDEX IDX_52EA1F09E238517C ON order_item (order_ref_id);
CREATE INDEX IDX_52EA1F09CCD7E912 ON order_item (menu_id);

-- Historique horodate des statuts d'une commande (suivi client).
CREATE TABLE order_status_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    order_ref_id INTEGER NOT NULL,
    changed_by_id INTEGER DEFAULT NULL,
    status VARCHAR(30) NOT NULL,
    changed_at DATETIME NOT NULL,
    CONSTRAINT FK_471BB88CE238517C FOREIGN KEY (order_ref_id) REFERENCES "order" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
    CONSTRAINT FK_471BB88C828AD0A0 FOREIGN KEY (changed_by_id) REFERENCES user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
);
CREATE INDEX IDX_471BB88CE238517C ON order_status_history (order_ref_id);
CREATE INDEX IDX_471BB88C828AD0A0 ON order_status_history (changed_by_id);

-- Avis clients (note de 1 a 5), publies apres validation par un employe.
CREATE TABLE review (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    rating INTEGER NOT NULL,
    comment CLOB DEFAULT NULL,
    order_ref_id INTEGER NOT NULL,
    status VARCHAR(20) NOT NULL,
    CONSTRAINT FK_794381C6E238517C FOREIGN KEY (order_ref_id) REFERENCES "order" (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE
);
CREATE UNIQUE INDEX UNIQ_794381C6E238517C ON review (order_ref_id);

-- File de messages de Symfony Messenger.
CREATE TABLE messenger_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    body CLOB NOT NULL,
    headers CLOB NOT NULL,
    queue_name VARCHAR(190) NOT NULL,
    created_at DATETIME NOT NULL,
    available_at DATETIME NOT NULL,
    delivered_at DATETIME DEFAULT NULL
);
CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id);

-- Migrations Doctrine deja appliquees.
CREATE TABLE doctrine_migration_versions (
    version VARCHAR(191) NOT NULL,
    executed_at DATETIME DEFAULT NULL,
    execution_time INTEGER DEFAULT NULL,
    PRIMARY KEY (version)
);
