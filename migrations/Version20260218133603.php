<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218133603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "order" ADD COLUMN adresse_prestation VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE TEMPORARY TABLE __temp__order_item AS SELECT id, quantity, price_at_order, order_ref_id, product_id FROM order_item');
        $this->addSql('DROP TABLE order_item');
        $this->addSql('CREATE TABLE order_item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, quantity INTEGER NOT NULL, price_at_order DOUBLE PRECISION NOT NULL, order_ref_id INTEGER NOT NULL, product_id INTEGER DEFAULT NULL, menu_id INTEGER DEFAULT NULL, CONSTRAINT FK_52EA1F09E238517C FOREIGN KEY (order_ref_id) REFERENCES "order" (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_52EA1F094584665A FOREIGN KEY (product_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_52EA1F09CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO order_item (id, quantity, price_at_order, order_ref_id, product_id) SELECT id, quantity, price_at_order, order_ref_id, product_id FROM __temp__order_item');
        $this->addSql('DROP TABLE __temp__order_item');
        $this->addSql('CREATE INDEX IDX_52EA1F094584665A ON order_item (product_id)');
        $this->addSql('CREATE INDEX IDX_52EA1F09E238517C ON order_item (order_ref_id)');
        $this->addSql('CREATE INDEX IDX_52EA1F09CCD7E912 ON order_item (menu_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__review AS SELECT id, rating, comment, order_ref_id, status FROM review');
        $this->addSql('DROP TABLE review');
        $this->addSql('CREATE TABLE review (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rating INTEGER NOT NULL, comment CLOB DEFAULT NULL, order_ref_id INTEGER NOT NULL, status VARCHAR(20) NOT NULL, CONSTRAINT FK_794381C6E238517C FOREIGN KEY (order_ref_id) REFERENCES "order" (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO review (id, rating, comment, order_ref_id, status) SELECT id, rating, comment, order_ref_id, status FROM __temp__review');
        $this->addSql('DROP TABLE __temp__review');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_794381C6E238517C ON review (order_ref_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__order AS SELECT id, created_at, status, total_price, delivery_time, event_date, equipment_loan, equipment_return, nombre_personne, prix_livraison, cancellation_reason, user_id FROM "order"');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('CREATE TABLE "order" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_at DATETIME NOT NULL, status VARCHAR(20) NOT NULL, total_price DOUBLE PRECISION NOT NULL, delivery_time TIME DEFAULT NULL, event_date DATE DEFAULT NULL, equipment_loan BOOLEAN DEFAULT NULL, equipment_return BOOLEAN DEFAULT NULL, nombre_personne INTEGER DEFAULT NULL, prix_livraison DOUBLE PRECISION DEFAULT NULL, cancellation_reason CLOB DEFAULT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO "order" (id, created_at, status, total_price, delivery_time, event_date, equipment_loan, equipment_return, nombre_personne, prix_livraison, cancellation_reason, user_id) SELECT id, created_at, status, total_price, delivery_time, event_date, equipment_loan, equipment_return, nombre_personne, prix_livraison, cancellation_reason, user_id FROM __temp__order');
        $this->addSql('DROP TABLE __temp__order');
        $this->addSql('CREATE INDEX IDX_F5299398A76ED395 ON "order" (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__order_item AS SELECT id, quantity, price_at_order, order_ref_id, product_id FROM order_item');
        $this->addSql('DROP TABLE order_item');
        $this->addSql('CREATE TABLE order_item (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, quantity INTEGER NOT NULL, price_at_order DOUBLE PRECISION NOT NULL, order_ref_id INTEGER NOT NULL, product_id INTEGER NOT NULL, CONSTRAINT FK_52EA1F09E238517C FOREIGN KEY (order_ref_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_52EA1F094584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO order_item (id, quantity, price_at_order, order_ref_id, product_id) SELECT id, quantity, price_at_order, order_ref_id, product_id FROM __temp__order_item');
        $this->addSql('DROP TABLE __temp__order_item');
        $this->addSql('CREATE INDEX IDX_52EA1F09E238517C ON order_item (order_ref_id)');
        $this->addSql('CREATE INDEX IDX_52EA1F094584665A ON order_item (product_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__review AS SELECT id, rating, comment, status, order_ref_id FROM review');
        $this->addSql('DROP TABLE review');
        $this->addSql('CREATE TABLE review (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rating INTEGER NOT NULL, comment CLOB DEFAULT NULL, status VARCHAR(20) DEFAULT \'pending\' NOT NULL, order_ref_id INTEGER NOT NULL, CONSTRAINT FK_794381C6E238517C FOREIGN KEY (order_ref_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO review (id, rating, comment, status, order_ref_id) SELECT id, rating, comment, status, order_ref_id FROM __temp__review');
        $this->addSql('DROP TABLE __temp__review');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_794381C6E238517C ON review (order_ref_id)');
    }
}
