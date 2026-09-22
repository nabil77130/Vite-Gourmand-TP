<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute sur User les colonnes necessaires a deux fonctionnalites :
 *
 *  - is_active : permet a l'administrateur de rendre inutilisable un compte
 *    employe sans le supprimer (depart de l'entreprise) ;
 *  - reset_token / reset_token_expires_at : reinitialisation du mot de passe
 *    par lien envoye par email, valable une heure et a usage unique.
 */
final class Version20260921080000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de is_active, reset_token et reset_token_expires_at sur la table user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD COLUMN is_active BOOLEAN DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN reset_token VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN reset_token_expires_at DATETIME DEFAULT NULL');

        // Les comptes deja presents restent actifs.
        $this->addSql('UPDATE user SET is_active = 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password, first_name, last_name, address, city, zip_code, phone, pays FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, zip_code VARCHAR(20) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, pays VARCHAR(100) DEFAULT NULL)');
        $this->addSql('INSERT INTO user (id, email, roles, password, first_name, last_name, address, city, zip_code, phone, pays) SELECT id, email, roles, password, first_name, last_name, address, city, zip_code, phone, pays FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
    }
}
