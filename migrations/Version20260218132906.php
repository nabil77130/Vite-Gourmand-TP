<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218132906 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "order" ADD COLUMN cancellation_reason CLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__order AS SELECT id, created_at, status, total_price, delivery_time, event_date, equipment_loan, equipment_return, nombre_personne, prix_livraison, user_id FROM "order"');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('CREATE TABLE "order" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_at DATETIME NOT NULL, status VARCHAR(20) NOT NULL, total_price DOUBLE PRECISION NOT NULL, delivery_time TIME DEFAULT NULL, event_date DATE DEFAULT NULL, equipment_loan BOOLEAN DEFAULT NULL, equipment_return BOOLEAN DEFAULT NULL, nombre_personne INTEGER DEFAULT NULL, prix_livraison DOUBLE PRECISION DEFAULT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO "order" (id, created_at, status, total_price, delivery_time, event_date, equipment_loan, equipment_return, nombre_personne, prix_livraison, user_id) SELECT id, created_at, status, total_price, delivery_time, event_date, equipment_loan, equipment_return, nombre_personne, prix_livraison, user_id FROM __temp__order');
        $this->addSql('DROP TABLE __temp__order');
        $this->addSql('CREATE INDEX IDX_F5299398A76ED395 ON "order" (user_id)');
    }
}
