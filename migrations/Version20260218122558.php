<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218122558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu ADD COLUMN min_people INTEGER DEFAULT NULL');
        $this->addSql('ALTER TABLE menu ADD COLUMN stock INTEGER DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__menu AS SELECT id, name, description, price FROM menu');
        $this->addSql('DROP TABLE menu');
        $this->addSql('CREATE TABLE menu (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, price NUMERIC(10, 2) NOT NULL)');
        $this->addSql('INSERT INTO menu (id, name, description, price) SELECT id, name, description, price FROM __temp__menu');
        $this->addSql('DROP TABLE __temp__menu');
    }
}
