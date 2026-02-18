<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218131054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE horaire (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, jour VARCHAR(20) NOT NULL, heure_ouverture VARCHAR(10) DEFAULT NULL, heure_fermeture VARCHAR(10) DEFAULT NULL, ferme BOOLEAN NOT NULL)');
        $this->addSql('ALTER TABLE "order" ADD COLUMN nombre_personne INTEGER DEFAULT NULL');
        $this->addSql('ALTER TABLE "order" ADD COLUMN prix_livraison DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN pays VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE horaire');
        $this->addSql('CREATE TEMPORARY TABLE __temp__order AS SELECT id, created_at, status, total_price, delivery_time, event_date, equipment_loan, equipment_return, user_id FROM "order"');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('CREATE TABLE "order" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_at DATETIME NOT NULL, status VARCHAR(20) NOT NULL, total_price DOUBLE PRECISION NOT NULL, delivery_time TIME DEFAULT NULL, event_date DATE DEFAULT NULL, equipment_loan BOOLEAN DEFAULT NULL, equipment_return BOOLEAN DEFAULT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO "order" (id, created_at, status, total_price, delivery_time, event_date, equipment_loan, equipment_return, user_id) SELECT id, created_at, status, total_price, delivery_time, event_date, equipment_loan, equipment_return, user_id FROM __temp__order');
        $this->addSql('DROP TABLE __temp__order');
        $this->addSql('CREATE INDEX IDX_F5299398A76ED395 ON "order" (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password, first_name, last_name, address, city, zip_code, phone FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, zip_code VARCHAR(20) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL)');
        $this->addSql('INSERT INTO user (id, email, roles, password, first_name, last_name, address, city, zip_code, phone) SELECT id, email, roles, password, first_name, last_name, address, city, zip_code, phone FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
    }
}
