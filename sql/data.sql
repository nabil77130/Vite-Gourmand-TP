-- =====================================================================
-- Vite & Gourmand - Jeu de donnees de demonstration
-- SGBD : SQLite 3 (base utilisee par l'application : var/data.db)
-- Genere a partir de la base de l'application le 23/09/2026.
-- =====================================================================

-- A executer apres sql/schema.sql.
--
-- Comptes de test (mot de passe identique pour les trois : password)
--   admin@vite-gourmand.com     administrateur (Julie)
--   employee@vite-gourmand.com  employe (Marc)
--   user@vite-gourmand.com      client (Jean Dupont)
-- Les mots de passe sont stockes haches (bcrypt), jamais en clair.

PRAGMA foreign_keys = ON;
BEGIN TRANSACTION;

-- Comptes : clients, employes et administrateur (roles en JSON).
INSERT INTO user (id, email, roles, password, first_name, last_name, address, city, zip_code, phone, pays, is_active, reset_token, reset_token_expires_at) VALUES (53, 'admin@vite-gourmand.com', '["ROLE_ADMIN"]', '$2y$13$FFGX342lHdoIxYxO6MAsg.XqHAyGYLqgDd6uN4A2EtDcLjO.NIoK.', 'Julie', 'Manager', NULL, NULL, NULL, '0600000000', NULL, 1, NULL, NULL);
INSERT INTO user (id, email, roles, password, first_name, last_name, address, city, zip_code, phone, pays, is_active, reset_token, reset_token_expires_at) VALUES (54, 'employee@vite-gourmand.com', '["ROLE_EMPLOYEE"]', '$2y$13$FFGX342lHdoIxYxO6MAsg.XqHAyGYLqgDd6uN4A2EtDcLjO.NIoK.', 'Marc', 'Employé', NULL, NULL, NULL, '0611111111', NULL, 1, NULL, NULL);
INSERT INTO user (id, email, roles, password, first_name, last_name, address, city, zip_code, phone, pays, is_active, reset_token, reset_token_expires_at) VALUES (55, 'user@vite-gourmand.com', '["ROLE_USER"]', '$2y$13$FFGX342lHdoIxYxO6MAsg.XqHAyGYLqgDd6uN4A2EtDcLjO.NIoK.', 'Jean', 'Dupont', '123 Rue de Paris', 'Paris', '75001', '0612345678', NULL, 1, NULL, NULL);

-- Allergenes (gluten, arachides...).
INSERT INTO allergen (id, name) VALUES (65, 'Arachides');
INSERT INTO allergen (id, name) VALUES (66, 'Produits Laitiers');
INSERT INTO allergen (id, name) VALUES (67, 'Gluten');
INSERT INTO allergen (id, name) VALUES (68, 'Soja');

-- Regimes alimentaires (vegetarien, vegan...).
INSERT INTO diet (id, name) VALUES (65, 'Végétarien');
INSERT INTO diet (id, name) VALUES (66, 'Vegan');
INSERT INTO diet (id, name) VALUES (67, 'Sans Gluten');
INSERT INTO diet (id, name) VALUES (68, 'Halal');

-- Thematiques (italien, asiatique...).
INSERT INTO theme (id, name) VALUES (65, 'Italien');
INSERT INTO theme (id, name) VALUES (66, 'Asiatique');
INSERT INTO theme (id, name) VALUES (67, 'Français');
INSERT INTO theme (id, name) VALUES (68, 'Mexicain');

-- Horaires d'ouverture affiches en pied de page.
INSERT INTO horaire (id, jour, heure_ouverture, heure_fermeture, ferme) VALUES (113, 'lundi', '08:00', '19:00', 0);
INSERT INTO horaire (id, jour, heure_ouverture, heure_fermeture, ferme) VALUES (114, 'mardi', '08:00', '19:00', 0);
INSERT INTO horaire (id, jour, heure_ouverture, heure_fermeture, ferme) VALUES (115, 'mercredi', '08:00', '19:00', 0);
INSERT INTO horaire (id, jour, heure_ouverture, heure_fermeture, ferme) VALUES (116, 'jeudi', '08:00', '19:00', 0);
INSERT INTO horaire (id, jour, heure_ouverture, heure_fermeture, ferme) VALUES (117, 'vendredi', '08:00', '19:00', 0);
INSERT INTO horaire (id, jour, heure_ouverture, heure_fermeture, ferme) VALUES (118, 'samedi', '09:00', '17:00', 0);
INSERT INTO horaire (id, jour, heure_ouverture, heure_fermeture, ferme) VALUES (119, 'dimanche', NULL, NULL, 1);

-- Plats (entree, plat, dessert, boisson).
INSERT INTO product (id, name, description, price, image_name, is_available, category) VALUES (129, 'Bruschetta', 'Tartines de tomates et basilic', 6.5, 'bruschetta.jpg', 1, 'starter');
INSERT INTO product (id, name, description, price, image_name, is_available, category) VALUES (130, 'Rouleaux de Printemps', 'Rouleaux vietnamiens croustillants', 5.0, 'spring_rolls.jpg', 1, 'starter');
INSERT INTO product (id, name, description, price, image_name, is_available, category) VALUES (131, 'Pizza Margherita', 'Tomate, mozzarella, basilic', 12.0, 'pizza_margherita.jpg', 1, 'main');
INSERT INTO product (id, name, description, price, image_name, is_available, category) VALUES (132, 'Pad Thai', 'Nouilles de riz au tofu', 14.5, 'pad_thai.jpg', 1, 'main');
INSERT INTO product (id, name, description, price, image_name, is_available, category) VALUES (133, 'Tiramisu', 'Dessert au café', 7.0, 'tiramisu.jpg', 1, 'dessert');
INSERT INTO product (id, name, description, price, image_name, is_available, category) VALUES (134, 'Salade de Fruits', 'Fruits de saison frais', 5.0, 'fruit_salad.webp', 1, 'dessert');
INSERT INTO product (id, name, description, price, image_name, is_available, category) VALUES (135, 'Coca Cola', 'Canette 33cl', 2.5, 'coca_cola.jpg', 1, 'drink');
INSERT INTO product (id, name, description, price, image_name, is_available, category) VALUES (136, 'Evian', 'Bouteille 50cl', 2.0, 'evian.webp', 1, 'drink');

-- Menus proposes a la commande. stock = nombre de commandes encore possibles (NULL = illimite).
INSERT INTO menu (id, name, description, price, min_people, stock, image_name, conditions) VALUES (49, 'Festin Italien', 'Une expérience complète italienne avec entrée, plat et dessert.', 22, 10, 15, 'festin_italien.jpg', 'Ce menu doit être commandé au moins 7 jours avant la prestation.
Les plats sont livrés dans des plats de service prêtés par l''entreprise : ils devront être restitués sous 10 jours ouvrés après la prestation.
Conserver au frais jusqu''au service.');
INSERT INTO menu (id, name, description, price, min_people, stock, image_name, conditions) VALUES (50, 'Menu Végétarien', 'Un menu 100% végétarien, frais et savoureux.', 18, 5, 20, 'menu_vegetarien.jpg', 'Commande à passer au moins 3 jours avant la prestation.
Les produits frais doivent être consommés dans les 24 heures suivant la livraison.');
INSERT INTO menu (id, name, description, price, min_people, stock, image_name, conditions) VALUES (51, 'Voyage Asiatique', 'Une entrée fraîche et un plat wok parfumé, pour un buffet dépaysant.', 24, 8, 12, NULL, 'Commande à passer au moins 5 jours avant la prestation.
Le wok est livré chaud dans un conteneur isotherme prêté par l''entreprise, à restituer sous 10 jours ouvrés.
Ce menu contient des arachides et du soja : prévenez-nous en cas d''allergie parmi vos convives.');
INSERT INTO menu (id, name, description, price, min_people, stock, image_name, conditions) VALUES (52, 'Douceur Vegan', 'Un menu léger et 100% végétal, sans gluten, idéal pour un cocktail.', 16, 5, 25, NULL, 'Commande à passer au moins 48 heures avant la prestation.
Les fruits sont préparés le matin même : à consommer le jour de la livraison.');

-- Liaison plat <-> allergene.
INSERT INTO product_allergen (product_id, allergen_id) VALUES (130, 67);
INSERT INTO product_allergen (product_id, allergen_id) VALUES (131, 66);
INSERT INTO product_allergen (product_id, allergen_id) VALUES (131, 67);
INSERT INTO product_allergen (product_id, allergen_id) VALUES (132, 65);
INSERT INTO product_allergen (product_id, allergen_id) VALUES (132, 68);
INSERT INTO product_allergen (product_id, allergen_id) VALUES (133, 66);
INSERT INTO product_allergen (product_id, allergen_id) VALUES (133, 67);

-- Liaison plat <-> regime.
INSERT INTO product_diet (product_id, diet_id) VALUES (129, 65);
INSERT INTO product_diet (product_id, diet_id) VALUES (131, 65);
INSERT INTO product_diet (product_id, diet_id) VALUES (132, 67);
INSERT INTO product_diet (product_id, diet_id) VALUES (133, 65);
INSERT INTO product_diet (product_id, diet_id) VALUES (134, 66);
INSERT INTO product_diet (product_id, diet_id) VALUES (134, 67);

-- Liaison plat <-> thematique.
INSERT INTO product_theme (product_id, theme_id) VALUES (129, 65);
INSERT INTO product_theme (product_id, theme_id) VALUES (130, 66);
INSERT INTO product_theme (product_id, theme_id) VALUES (131, 65);
INSERT INTO product_theme (product_id, theme_id) VALUES (132, 66);
INSERT INTO product_theme (product_id, theme_id) VALUES (133, 65);
INSERT INTO product_theme (product_id, theme_id) VALUES (134, 67);

-- Composition d'un menu (liaison menu <-> plat).
INSERT INTO menu_product (menu_id, product_id) VALUES (49, 129);
INSERT INTO menu_product (menu_id, product_id) VALUES (49, 131);
INSERT INTO menu_product (menu_id, product_id) VALUES (49, 133);
INSERT INTO menu_product (menu_id, product_id) VALUES (50, 129);
INSERT INTO menu_product (menu_id, product_id) VALUES (50, 130);
INSERT INTO menu_product (menu_id, product_id) VALUES (51, 130);
INSERT INTO menu_product (menu_id, product_id) VALUES (51, 132);
INSERT INTO menu_product (menu_id, product_id) VALUES (52, 134);

-- Liaison menu <-> thematique (filtres de la carte).
INSERT INTO menu_theme (menu_id, theme_id) VALUES (49, 65);
INSERT INTO menu_theme (menu_id, theme_id) VALUES (51, 66);
INSERT INTO menu_theme (menu_id, theme_id) VALUES (52, 67);

-- Liaison menu <-> regime (filtres de la carte).
INSERT INTO menu_diet (menu_id, diet_id) VALUES (50, 65);
INSERT INTO menu_diet (menu_id, diet_id) VALUES (52, 66);
INSERT INTO menu_diet (menu_id, diet_id) VALUES (52, 67);

-- Galerie de photos d'un menu (chemin relatif au dossier public/).
INSERT INTO menu_image (id, menu_id, path, alt, position) VALUES (33, 49, 'images/menus/festin_italien.jpg', 'Photo 1 du menu Festin Italien', 0);
INSERT INTO menu_image (id, menu_id, path, alt, position) VALUES (34, 49, 'images/products/bruschetta.jpg', 'Photo 2 du menu Festin Italien', 1);
INSERT INTO menu_image (id, menu_id, path, alt, position) VALUES (35, 49, 'images/products/pizza_margherita.jpg', 'Photo 3 du menu Festin Italien', 2);
INSERT INTO menu_image (id, menu_id, path, alt, position) VALUES (36, 49, 'images/products/tiramisu.jpg', 'Photo 4 du menu Festin Italien', 3);
INSERT INTO menu_image (id, menu_id, path, alt, position) VALUES (37, 50, 'images/menus/menu_vegetarien.jpg', 'Photo 1 du menu Menu Végétarien', 0);
INSERT INTO menu_image (id, menu_id, path, alt, position) VALUES (38, 50, 'images/products/spring_rolls.jpg', 'Photo 2 du menu Menu Végétarien', 1);
INSERT INTO menu_image (id, menu_id, path, alt, position) VALUES (39, 51, 'images/products/pad_thai.jpg', 'Photo 1 du menu Voyage Asiatique', 0);
INSERT INTO menu_image (id, menu_id, path, alt, position) VALUES (40, 51, 'images/products/spring_rolls.jpg', 'Photo 2 du menu Voyage Asiatique', 1);
INSERT INTO menu_image (id, menu_id, path, alt, position) VALUES (41, 52, 'images/products/fruit_salad.webp', 'Photo 1 du menu Douceur Vegan', 0);
INSERT INTO menu_image (id, menu_id, path, alt, position) VALUES (42, 52, 'images/products/fruit_salad.jpg', 'Photo 2 du menu Douceur Vegan', 1);

-- Commandes des clients. "order" est un mot reserve SQL, d'ou les guillemets.
INSERT INTO "order" (id, created_at, status, total_price, user_id, delivery_time, event_date, equipment_loan, equipment_return, nombre_personne, prix_livraison, cancellation_reason, adresse_prestation) VALUES (53, '2026-09-21 00:18:34', 'delivered', 316.8, 55, '19:30:00', '2026-09-28', 1, 0, 16, 0.0, NULL, '24 cours de l''Intendance, Bordeaux');
INSERT INTO "order" (id, created_at, status, total_price, user_id, delivery_time, event_date, equipment_loan, equipment_return, nombre_personne, prix_livraison, cancellation_reason, adresse_prestation) VALUES (54, '2026-09-14 00:18:34', 'completed', 273.72, 55, '19:30:00', '2026-10-05', 0, 0, 12, 9.72, NULL, '8 avenue de la Marne, Mérignac');
INSERT INTO "order" (id, created_at, status, total_price, user_id, delivery_time, event_date, equipment_loan, equipment_return, nombre_personne, prix_livraison, cancellation_reason, adresse_prestation) VALUES (55, '2026-09-18 00:18:34', 'delivered', 240.0, 55, '19:30:00', '2026-10-01', 1, 0, 10, 0.0, NULL, '15 quai des Chartrons, Bordeaux');
INSERT INTO "order" (id, created_at, status, total_price, user_id, delivery_time, event_date, equipment_loan, equipment_return, nombre_personne, prix_livraison, cancellation_reason, adresse_prestation) VALUES (56, '2026-09-09 00:18:34', 'completed', 162.0, 55, '19:30:00', '2026-10-10', 0, 0, 10, 0.0, NULL, '3 rue Sainte-Catherine, Bordeaux');
INSERT INTO "order" (id, created_at, status, total_price, user_id, delivery_time, event_date, equipment_loan, equipment_return, nombre_personne, prix_livraison, cancellation_reason, adresse_prestation) VALUES (57, '2026-09-22 00:18:34', 'pending', 103.95, 55, '19:30:00', '2026-09-27', 0, 0, 6, 7.95, NULL, '52 avenue Roul, Talence');

-- Ligne de commande : le menu commande.
INSERT INTO order_item (id, quantity, price_at_order, order_ref_id, product_id, menu_id) VALUES (43, 1, 22.0, 53, NULL, 49);
INSERT INTO order_item (id, quantity, price_at_order, order_ref_id, product_id, menu_id) VALUES (44, 1, 22.0, 54, NULL, 49);
INSERT INTO order_item (id, quantity, price_at_order, order_ref_id, product_id, menu_id) VALUES (45, 1, 24.0, 55, NULL, 51);
INSERT INTO order_item (id, quantity, price_at_order, order_ref_id, product_id, menu_id) VALUES (46, 1, 18.0, 56, NULL, 50);
INSERT INTO order_item (id, quantity, price_at_order, order_ref_id, product_id, menu_id) VALUES (47, 1, 16.0, 57, NULL, 52);

-- Historique horodate des statuts d'une commande (suivi client).
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (99, 53, 55, 'pending', '2026-09-21 00:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (100, 53, 54, 'accepted', '2026-09-21 05:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (101, 53, 54, 'preparing', '2026-09-21 10:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (102, 53, 54, 'delivering', '2026-09-21 15:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (103, 53, 54, 'delivered', '2026-09-21 20:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (104, 54, 55, 'pending', '2026-09-14 00:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (105, 54, 54, 'accepted', '2026-09-14 05:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (106, 54, 54, 'preparing', '2026-09-14 10:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (107, 54, 54, 'delivering', '2026-09-14 15:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (108, 54, 54, 'delivered', '2026-09-14 20:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (109, 54, 54, 'completed', '2026-09-15 01:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (110, 55, 55, 'pending', '2026-09-18 00:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (111, 55, 54, 'accepted', '2026-09-18 05:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (112, 55, 54, 'preparing', '2026-09-18 10:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (113, 55, 54, 'delivering', '2026-09-18 15:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (114, 55, 54, 'delivered', '2026-09-18 20:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (115, 56, 55, 'pending', '2026-09-09 00:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (116, 56, 54, 'accepted', '2026-09-09 05:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (117, 56, 54, 'preparing', '2026-09-09 10:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (118, 56, 54, 'delivering', '2026-09-09 15:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (119, 56, 54, 'delivered', '2026-09-09 20:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (120, 56, 54, 'completed', '2026-09-10 01:18:34');
INSERT INTO order_status_history (id, order_ref_id, changed_by_id, status, changed_at) VALUES (121, 57, 55, 'pending', '2026-09-22 00:18:34');

-- Avis clients (note de 1 a 5), publies apres validation par un employe.
INSERT INTO review (id, rating, comment, order_ref_id, status) VALUES (33, 5, 'Excellent repas, arrivé chaud !', 53, 'approved');
INSERT INTO review (id, rating, comment, order_ref_id, status) VALUES (34, 4, 'Très bon, mais un peu de retard sur la livraison.', 54, 'approved');

-- Migrations Doctrine deja appliquees.
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20260218091033', '2026-02-18 17:23:40', 48);
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20260218095423', '2026-02-18 17:23:40', 4);
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20260218122558', '2026-02-18 17:23:40', 1);
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20260218131054', '2026-02-18 17:23:40', 1);
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20260218132906', '2026-02-18 17:23:40', 1);
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20260218133227', '2026-02-18 17:23:40', 1);
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20260218133603', '2026-02-18 17:23:40', 3);
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20260921070000', '2026-09-21 07:01:18', 3);
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20260921080000', '2026-09-21 17:49:48', 36);
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20260922210000', '2026-09-22 21:19:26', 34);
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20260922220000', '2026-09-22 22:07:53', 4);
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES ('DoctrineMigrations\Version20260923000000', '2026-09-23 00:06:29', 3);

COMMIT;
