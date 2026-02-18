<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218095423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE allergen (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE diet (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE menu (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, price NUMERIC(10, 2) NOT NULL)');
        $this->addSql('CREATE TABLE menu_product (menu_id INTEGER NOT NULL, product_id INTEGER NOT NULL, PRIMARY KEY (menu_id, product_id), CONSTRAINT FK_5B911913CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5B9119134584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_5B911913CCD7E912 ON menu_product (menu_id)');
        $this->addSql('CREATE INDEX IDX_5B9119134584665A ON menu_product (product_id)');
        $this->addSql('CREATE TABLE product_allergen (product_id INTEGER NOT NULL, allergen_id INTEGER NOT NULL, PRIMARY KEY (product_id, allergen_id), CONSTRAINT FK_EE0F62594584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_EE0F62596E775A4A FOREIGN KEY (allergen_id) REFERENCES allergen (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_EE0F62594584665A ON product_allergen (product_id)');
        $this->addSql('CREATE INDEX IDX_EE0F62596E775A4A ON product_allergen (allergen_id)');
        $this->addSql('CREATE TABLE product_diet (product_id INTEGER NOT NULL, diet_id INTEGER NOT NULL, PRIMARY KEY (product_id, diet_id), CONSTRAINT FK_100C47814584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_100C4781E1E13ACE FOREIGN KEY (diet_id) REFERENCES diet (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_100C47814584665A ON product_diet (product_id)');
        $this->addSql('CREATE INDEX IDX_100C4781E1E13ACE ON product_diet (diet_id)');
        $this->addSql('CREATE TABLE product_theme (product_id INTEGER NOT NULL, theme_id INTEGER NOT NULL, PRIMARY KEY (product_id, theme_id), CONSTRAINT FK_36299C544584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_36299C5459027487 FOREIGN KEY (theme_id) REFERENCES theme (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_36299C544584665A ON product_theme (product_id)');
        $this->addSql('CREATE INDEX IDX_36299C5459027487 ON product_theme (theme_id)');
        $this->addSql('CREATE TABLE theme (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)');
        $this->addSql('ALTER TABLE "order" ADD COLUMN delivery_time TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE "order" ADD COLUMN event_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE "order" ADD COLUMN equipment_loan BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE "order" ADD COLUMN equipment_return BOOLEAN DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE allergen');
        $this->addSql('DROP TABLE diet');
        $this->addSql('DROP TABLE menu');
        $this->addSql('DROP TABLE menu_product');
        $this->addSql('DROP TABLE product_allergen');
        $this->addSql('DROP TABLE product_diet');
        $this->addSql('DROP TABLE product_theme');
        $this->addSql('DROP TABLE theme');
        $this->addSql('CREATE TEMPORARY TABLE __temp__order AS SELECT id, created_at, status, total_price, user_id FROM "order"');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('CREATE TABLE "order" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_at DATETIME NOT NULL, status VARCHAR(20) NOT NULL, total_price DOUBLE PRECISION NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO "order" (id, created_at, status, total_price, user_id) SELECT id, created_at, status, total_price, user_id FROM __temp__order');
        $this->addSql('DROP TABLE __temp__order');
        $this->addSql('CREATE INDEX IDX_F5299398A76ED395 ON "order" (user_id)');
    }
}
