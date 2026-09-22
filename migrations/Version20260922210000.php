<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute le champ "conditions" sur la table menu.
 *
 * L'enonce demande que chaque menu porte ses propres conditions (delai de
 * commande, precautions de stockage, materiel prete) et qu'elles soient mises
 * bien en evidence avant la commande.
 */
final class Version20260922210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la colonne conditions sur la table menu';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE menu ADD COLUMN conditions CLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__menu AS SELECT id, name, description, price, min_people, stock, image_name FROM menu');
        $this->addSql('DROP TABLE menu');
        $this->addSql('CREATE TABLE menu (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, price NUMERIC(10, 2) NOT NULL, min_people INTEGER DEFAULT NULL, stock INTEGER DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO menu (id, name, description, price, min_people, stock, image_name) SELECT id, name, description, price, min_people, stock, image_name FROM __temp__menu');
        $this->addSql('DROP TABLE __temp__menu');
    }
}
